<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class AssociateLondonEateryAreasCommand extends Command
{
    protected $signature = 'one-time:coeliac:associate-london-eatery-areas';

    public function handle(): void
    {
        /** @phpstan-ignore-next-line  */
        EateryCounty::query()
            ->where('slug', 'london')
            ->first()
            ->eateries()
            ->chaperone()
            ->with(['town'])
            ->whereNull('area_id')
            ->where('live', true)
            ->lazy()
            ->each($this->processLocation(...));

        /** @phpstan-ignore-next-line  */
        EateryCounty::query()
            ->where('slug', 'london')
            ->first()
            ->nationwideBranches()
            ->chaperone()
            ->with(['town', 'eatery'])
            ->whereNull('area_id')
            ->where('live', true)
            ->lazy()
            ->each($this->processLocation(...));
    }

    protected function processLocation(Eatery|NationwideBranch $model): void
    {
        $address = str_replace("\n", ', ', $model->address);

        $name = $model->name;

        if ($model instanceof NationwideBranch && ! $name) {
            $name = $model->eatery->name;
        }

        /** @var EateryTown $town */
        $town = $model->town;

        $prompt = <<<PROMPT
                For the given eating out location in London,

                Name: {$name},
                Borough: {$town->town},
                Address: {$address},

                Please determine the area within that borough that the eatery resides in, this should be a well known area within that borough and the address of the eatery should be within that area. The area you give will be used as an index on the eatery and as part of SEO.

                If the given eatery is in a well known shopping center or area, such as Brents Cross for example, then please use that as the area.

                Please do not give the area of the same name as the borough, as the area is a sub filter of borough, and they shouldn't be the same.

                Please return your response as a JSON string with the following keys, area, with the area of that borough that the eatery is in, and a key of explanation, with your reasoning.

                Please return nothing else except the JSON as described above.
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

            if ( ! array_key_exists('area', $json)) {
                $this->error("invalid json returned {$response} {$name}");

                return;
            }

            $this->info("Eatery: {$name}");
            $this->info("Borough: {$town->town}");
            $this->info("Address: {$address}");
            $this->info("Suggested Area: {$json['area']}");
            $this->info("Reason: {$json['explanation']}");

            //            if ($this->confirm('Set the given area?', true)) {
            $area = EateryArea::query()->firstOrCreate([
                'area' => $json['area'],
                'town_id' => $model->town_id,
            ]);

            $model->updateQuietly(['area_id' => $area->id]);
            //            }
        }
    }
}
