<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Shop\ShopProduct;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('gpt-4o-mini')]
class SealiacProductOverviewAgent implements Agent
{
    use Promptable;

    public function __construct(protected ShopProduct $product)
    {
        $this->product->loadMissing('reviews');
    }

    public function instructions(): Stringable|string
    {
        return view('prompts.sealiac-product-overview', [
            'product' => $this->product,
        ])->render();
    }
}
