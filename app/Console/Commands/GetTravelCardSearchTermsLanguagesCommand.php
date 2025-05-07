<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class GetTravelCardSearchTermsLanguagesCommand extends Command
{
    protected $signature = 'one-time:coeliac:get-travel-card-search-terms-languages';

    public function handle(): void
    {
        $count = TravelCardSearchTerm::query()->where('type', 'country')->count();
        $progress = $this->output->createProgressBar($count);

        ShopCategory::query()->where('title', 'Coeliac Gluten Free Travel Cards')
            ->firstOrFail()
            ->products()
            ->whereHas('travelCardSearchTerms', fn (Builder $query) => $query->where('type', 'country')) /** @phpstan-ignore-line  */
            ->get()
            ->each(function (ShopProduct $product) use ($progress): void {
                $languages = Str::before($product->title, ' Coeliac Gluten Free Travel Translation Card');

                $product->travelCardSearchTerms
//                    ->reject(fn(TravelCardSearchTerm $searchTerm) => $searchTerm->pivot->card_language !== null)
                    ->each(function (TravelCardSearchTerm $searchTerm) use ($languages, $progress): void {
                        $prompt = <<<PROMPT
                        From the given country, {$searchTerm->term}, and these two language {$languages}, return which of those two languages
                        is most likely to spoken in that country, and a score of what percentage of people in that country will speak that language,
                        please take into account all languages spoken in that country, not just the two examples, if it is a minority language,
                        give a lower score.

                        For example, spain and spanish might be 100%, for brazil and spain might be 5%, as an example.

                        Please return as a json object with the keys language and score, language is a string and must only be one of the two
                        given above ({$languages}) - and score is an int from 0 - 100 of the percentage of people in that country that speak that language.
                        Please also include an explanation key. Nothing else. Not a percentage of how sure you are or the likeliness. IE, if the Spanish is
                        spoken by 5% of people in the requested country, then return 5%.

                        If the language could be spoken in both countries, please return a string of `both` for the language key, and the score the higher of
                        the two options, for example, if the languages were Chinese Traditional and Chinese Simplified, then Chinese would apply to both
                        countries, so you would return `both` as the language key.

                        Do not return a country not given in the list.

                        Please return the country as it should be spelt, title case etc, so Spanish, French, etc.
                        PROMPT;

                        $result = OpenAI::chat()->create([
                            'model' => 'gpt-3.5-turbo-1106',
                            'messages' => [
                                ['role' => 'system', 'content' => $prompt],
                            ],
                        ]);

                        /** @var string $response */
                        $response = $result->choices[0]->message->content;

                        if (json_validate($response)) {
                            /** @var array $json */
                            $json = json_decode($response, true);

                            if ( ! array_key_exists('language', $json)) {
                                $this->error("invalid json returned {$response} for {$languages}");

                                return;
                            }

                            if ($json['language'] !== 'both' && ! Str::of($languages)->lower()->contains(mb_strtolower($json['language']))) {
                                $result = OpenAI::chat()->create([
                                    'model' => 'gpt-3.5-turbo-1106',
                                    'messages' => [
                                        ['role' => 'system', 'content' => $prompt],
                                        ['role' => 'assistant', 'content' => $response],
                                        ['role' => 'user', 'content' => "The language you returned, `{$json['language']}` is not one of the two I clearly requested, {$languages}, please try again sticking to the rules above, and the exact requested JSON structure, nothing else just JSON."],
                                    ],
                                ]);

                                if (json_validate($response)) {
                                    /** @var string $response */
                                    $response = $result->choices[0]->message->content;

                                    if (Str::contains($response, '```')) {
                                        $response = Str::of($response)->between('```', '```')->trim()->toString();
                                    }

                                    $this->info("Returned {$json['language']} for {$searchTerm->term} in {$languages}, done a retry, new response: {$response}");

                                    /** @var array $json */
                                    $json = json_decode($response, true);
                                }
                            }

                            /** @phpstan-ignore-next-line  */
                            $searchTerm->pivot->update([
                                'card_language' => $json['language'],
                                'card_score' => $json['score'],
                                'card_show_on_product_page' => true,
                            ]);
                        }

                        $progress->advance();
                    });
            });
    }
}
