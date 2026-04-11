<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\FindLinkForCountyTool;
use App\Ai\Tools\FindLinkForTownTool;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class EateryCountryDescriptionAgent implements Agent, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
Using the given information on a country, generate a short, SEO friendly description for eating gluten free in that country

The description is for a gluten free eating out guide on the website Coeliac Sanctuary, and should include the number of eateries in that county, and the number of counties listed in that county (assuming its more than 1).

Please also include some popular counties/cities/towns in that description where people will tend to go, and use the FindLinkForCounty and FindLinkForTown tools to find links to those pages, if no link is returned, dont include that location.

Include coeliac (Uk spelling) and gluten free (no hyphen) in the content for the SEO benefit

Dont force content in, make it sound natural, but seo friendly.

Free free to use bold for emphasis.

There is no need to mention Coeliac Sanctuary, or Eating out guide etc, as this is implied from the page of the website.
TEXT;

    }

    public function tools(): iterable
    {
        return [
            new FindLinkForCountyTool(),
            new FindLinkForTownTool(),
        ];
    }
}
