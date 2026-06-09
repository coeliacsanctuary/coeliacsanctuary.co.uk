<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class WhatAreTravelCardsTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Describe what our gluten free travel cards are any why you would want one';
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        return <<<'text'
        # Standard travel cards
        Travel safely with my double sided, extra thick, pre-printed Coeliac travel cards. Each card has two languages, perfect for dining gluten free in Spain, Egypt, Greece and beyond. Designed for Coeliacs without other allergies, they’re durable, compact, and make communicating your needs easy for stress free meals abroad.

        Our language cards explain Coeliac and the need to be gluten free. This card is double sided and contains two languages.

        These cards are credit card/business card sized which makes them easy to slip into your purse or wallet to take to different restaurants while abroad and are extra thick, sturdy and laminated on both sides to help them stand up to lots of usage.

        Translated by native speakers to ensure accuracy.

        # Coeliac+ travel cards
        Travel safely with my Coeliac+ cards, which clearly explain what to avoid as a Coeliac and let you mark extra allergies or dietary requirements. Perfect for dining out in France, Italy, Turkey and beyond, they’re double-sided, durable, and help ensure stress free gluten free and allergen safe meals abroad.

        One item is one double sided card in chosen language.

        Our Coeliac+ are designed to help Coeliacs with allergies and/or dietary needs to eat out safely, explaining what can't be eaten on a gluten free diet and tick boxes to mark other allergies or dietary needs or requirements such as Diabetic Friendly and Vegan.

        These cards contain the 14 top allergens as tick boxes as well as 3 blank spaces you can write in your own if you have allergies not in the UK top 14.

        Our cards are Credit Card/Business card sized to make them easy to slip into your purse or wallet to take to different restaurants and are extra thick and sturdy to help them stand up to lots of usage.

        Translated by native speakers to ensure accuracy.
        text;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
