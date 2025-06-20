<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class GetLondonBoroughIntoTestCommand extends Command
{
    protected $signature = 'one-time:coeliac:get-london-borough-intros';

    public function handle(): void
    {
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

            Please generate a nice, SEO friendly short paragraph on popular areas and/or tourist destinations and/or sights in that borough, for example, if the borough was Westminster, you could include Big Ben, Covent Garden etc.

            Areas/Tourist Destinations/Sights could include popular things like Big Ben, Madame Tussauds, Shopping Centers, Parks, Sports Stadiums/Teams, etc

            This short paragraph will be used on a listing page with all the other London Boroughs in an online Gluten Free eating guide across London.

            Please limit the paragraph to no more than 50 words or so.

            Pleas return only the short paragraph, and nothing else.
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
            'description' => $response,
        ]);

        $this->info("Updated {$borough->town}");
    }
}
