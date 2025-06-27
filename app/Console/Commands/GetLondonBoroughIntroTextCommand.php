<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class GetLondonBoroughIntroTextCommand extends Command
{
    protected $signature = 'one-time:coeliac:get-london-borough-intros';

    public function handle(): void
    {
        /** @phpstan-ignore-next-line  */
        EateryCounty::query()
            ->where('slug', 'london')
            ->first()
            ->towns()
            ->lazy()
            ->each($this->processBorough(...));
    }

    protected function processBorough(EateryTown $borough): void
    {
        $prompt = <<<PROMPT
            For the given london borough:,

            {$borough->town}

            Please generate a nice, SEO friendly introductory paragraphs on popular areas and/or tourist destinations and/or sights in that borough, for example, if the borough was Westminster, you could include Big Ben, Covent Garden etc.

            Areas/Tourist Destinations/Sights could include popular things like Big Ben, Madame Tussauds, Shopping Centers, Parks, Sports Stadiums/Teams, etc

            These paragraphs will be used on at the top of a dedicated page for that borough in a gluten free guide for places across london, and will serve as the above the fold introductory text on the page, so please also add many organic references to eating out in this borough, with special mention to gluten free (Spelled as is, no dashes) and make these the emphasis.

            Please limit the paragraphs to no more than 150 words or so, or if it makes sense, at least two paragraphs, but no more than 3.

            Please separate all new lines with the PHP newline character \n

            Please keep it organic, no 'welcome to' etc, this will go below a H1 of `Eating gluten free in {$borough->town}` which is already in place.

            Pleas return only the paragraph(s), and nothing else.
           PROMPT;

        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
            ],
        ]);

        /** @var string $response */
        $response = $result->choices[0]->message->content;

        $borough->updateQuietly([
            'intro_text' => $response,
        ]);

        $this->info("Updated {$borough->town}");
    }
}
