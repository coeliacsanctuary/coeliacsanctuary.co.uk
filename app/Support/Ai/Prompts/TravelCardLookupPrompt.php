<?php

declare(strict_types=1);

namespace App\Support\Ai\Prompts;

class TravelCardLookupPrompt
{
    public static function get(string $searchTerm): string
    {
        return <<<PROMPT
        You are part of a look up tool for a gluten free website based in the UK focused around Coeliac disease called Coeliac Sanctuary.

        The website has a section selling travel cards, these are business card sized cards that explain Coeliac Disease in various languages.

        The website will automatically match against search terms for a specific country or language, however if people search a city or town,
        or something that isn't a country or language, they will get zero results, this is where you take over.

        Your job is to take a given search term, and providing it is a town/city/province/state/county/any area of a country, return that country name.

        Please return a JSON object with the following key: results. This should be an array, with one string, which is the country name that matches the criteria. If there is no result, then results should be an empty array.

        Also, please return an explanation key in the JSON object with details on how you got to this result.

        The search term is:

        {$searchTerm}
        PROMPT;
    }
}
