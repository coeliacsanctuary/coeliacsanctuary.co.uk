<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use Exception;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

class CleanUpProductDescriptionsCommand extends Command
{
    protected $signature = 'one-time:coeliac:clean-up-product-descriptions';

    public function handle(): void
    {
        if ( ! $this->confirm('Are you sure you want to run this command?')) {
            return;
        }

        $this->standardCards();
        $this->plusCards();
    }

    protected function standardCards(): void
    {
        $this->info('Cleaning up standard cards');

        $products = ShopCategory::query()->where('title', 'Coeliac Gluten Free Travel Cards')
            ->firstOrFail()
            ->products()
            ->get();

        $products->each(function (ShopProduct $product): void {
            $this->line("Handling {$product->title}");

            $this->updateStandardProduct($product);
        });
    }

    protected function updateStandardProduct(ShopProduct $product): void
    {
        $prompt = <<<PROMPT
                Below is a product description for a coeliac translation card, it is double sided with a different language on each side.

                Currently, the description takes the format of:

                - The main product description. The last word is typically 'usage'.
                - Countries this product can be used in
                - The english translation of the text on the travel card, this may have an heading, or just start with "I have Coeliac Disease and follow a strict gluten free diet.", and might be wrapped in <em> tags.

                Your job it to take the given description, and then return just the core, main product description without the list
                of countries it can be used in, and the english translation.

                Please return the description as a JSON response, with the description in a key of `result`. Please also keep any formatting in the description,
                HTML and new line characters (\n) - do not replace new lines with html line breaks or convert to markdown! Ensure your response is valid json that can be decoded in PHP.

                The product description is:

                {$product->long_description}
                PROMPT;

        $result = $this->sendToAi($prompt);

        $this->updateProduct($result, $product, fn () => $this->updateStandardProduct($product));
    }

    protected function sendToAi(string $prompt): CreateResponse
    {
        return OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
            'tool_choice' => 'auto',
            'tools' => [[
                'type' => 'function',
                'function' => [
                    'name' => 'return_cleaned_description',
                    'description' => 'Return just the cleaned up main product description.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'result' => [
                                'type' => 'string',
                                'description' => 'The cleaned-up main product description with HTML and \n formatting preserved.',
                            ],
                        ],
                        'required' => ['result'],
                    ],
                ],
            ]],
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
            ],
        ]);
    }

    protected function updateProduct(CreateResponse $result, ShopProduct $product, callable $failure): void
    {
        try {
            /** @var string $response */
            $response = $result->choices[0]->message->toolCalls[0]->function->arguments ?? null;

            if ($response) {
                /** @var array $json */
                $json = json_decode($response, true);

                $this->line('New description:');
                $this->line($json['result']);

                if ($this->confirm('Update?', true)) {
                    $product->update([
                        'long_description' => $json['result'],
                    ]);

                    return;
                }
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $failure();
    }

    protected function plusCards(): void
    {
        $this->info('Cleaning up plus cards');

        $products = ShopCategory::query()->where('title', 'Coeliac+ Other Allergen Travel Cards')
            ->firstOrFail()
            ->products()
            ->get();

        $products->each(function (ShopProduct $product): void {
            $this->line("Handling {$product->title}");

            $this->updatePlusProduct($product);
        });
    }

    protected function updatePlusProduct(ShopProduct $product): void
    {
        $prompt = <<<PROMPT
                 Below is a product description for a coeliac translation card, one one side it explains coeliac disease in a native language,
                and on the other it contains checkboxes and space to list other allergens, this card is called a {$product->title}

                Currently, the description takes the format of:

                - The core, main product description
                - How the card reads in english, aka what it says, this maybe after a heading of 'Card reads the following' and includes the english versions of side 1 and side 2.
                - Optional link to a 'standard' card

                Your job it to take the given description, and then extract just the core, main product description without the english translation or the optional link.

                Please return the description as a JSON response, with the description in a key of `result`. Please also keep any formatting in the description,
                HTML and new line characters (\n) - do not replace new lines with html line breaks or convert to markdown! Ensure your response is valid json that can be decoded in PHP.

                #The product description is:

                {$product->long_description}
                PROMPT;

        $result = $this->sendToAi($prompt);

        $this->updateProduct($result, $product, fn () => $this->updatePlusProduct($product));
    }
}
