<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use App\Support\Ai\Prompts\ShopProductSealiacOverviewPrompt;
use Exception;
use OpenAI\Laravel\Facades\OpenAI;

class GetSealiacProductOverviewAction
{
    public function handle(ShopProduct $product): SealiacOverview
    {
        if ($product->sealiacOverview) {
            return $product->sealiacOverview;
        }

        if ($product->reviews()->count() === 0) {
            throw new Exception('No reviews found to generate overview');
        }

        $prompt = app(ShopProductSealiacOverviewPrompt::class)->handle($product);

        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
            ],
        ]);

        /** @var string $response */
        $response = $result->choices[0]->message->content;

        return SealiacOverview::query()->create([
            'model_id' => $product->id,
            'model_type' => ShopProduct::class,
            'overview' => $response,
        ]);
    }
}
