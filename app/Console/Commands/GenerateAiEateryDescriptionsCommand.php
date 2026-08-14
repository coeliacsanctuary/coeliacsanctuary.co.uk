<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Ai\Agents\EateryDescriptionAgent;
use App\Models\EateryAiDescription;
use App\Models\EatingOut\Eatery;
use Exception;
use Illuminate\Console\Command;

class GenerateAiEateryDescriptionsCommand extends Command
{
    protected $signature = 'coeliac:generate-ai-eatery-descriptions';

    public function handle(): void
    {
        Eatery::query()
            ->withCount('reviews')
            ->where('generated_ai_description', false)
            ->orderByDesc('reviews_count')
            ->with(['town'])
            ->take(20)
            ->lazy()
            ->each(function (Eatery $eatery): void {
                try {
                    $response = (new EateryDescriptionAgent($eatery))->prompt('Generate the description');

                    EateryAiDescription::query()->create([
                        'wheretoeat_id' => $eatery->id,
                        'description' => $response->text,
                    ]);

                    $eatery->updateQuietly([
                        'generated_ai_description' => true,
                        'updated_at' => $eatery->updated_at,
                    ]);

                    /** @phpstan-ignore-next-line  */
                    $this->info("Generated description for {$eatery->name} in {$eatery->town->town}");
                } catch (Exception $e) {
                    //
                }
            });
    }
}
