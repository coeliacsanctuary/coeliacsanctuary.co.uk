<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EateryAiDescription;
use App\Models\EatingOut\Eatery;
use Exception;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

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
                    $result = OpenAI::chat()->create([
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => view('prompts.eatery-description', ['eatery' => $eatery])->render()],
                        ],
                    ]);

                    $response = $result->choices[0]->message->content;

                    EateryAiDescription::query()->create([
                        'wheretoeat_id' => $eatery->id,
                        'description' => $response,
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
