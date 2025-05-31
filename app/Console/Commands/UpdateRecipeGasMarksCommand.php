<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipes\Recipe;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateRecipeGasMarksCommand extends Command
{
    protected $signature = 'one-time:coeliac:update-recipe-gas-marks';

    public function handle(): void
    {
        $recipes = Recipe::query()
            ->where('method', 'regexp', 'gas mark [0-9]+[^/0-9]')
            ->get();

        $progress = $this->output->createProgressBar($recipes->count());

        $gasMarkMap = [
            1 => 140,
            2 => 150,
            3 => 160,
            4 => 180,
            5 => 190,
            6 => 200,
            7 => 220,
            8 => 230,
            9 => 240,
        ];

        try {
            DB::beginTransaction();
            $recipes->each(function (Recipe $recipe) use ($progress, $gasMarkMap): void {
                try {
                    $method = Str::of($recipe->method)
                        ->replaceMatches('/gas mark ([0-9]+)/i', fn ($matches) => "{$matches[0]}/{$gasMarkMap[$matches[1]]}c")
                        ->toString();

                    $recipe->updateQuietly([
                        'method' => $method,
                    ]);
                } catch (Exception $e) {
                    $location = Str::position($recipe->method, 'gas mark');
                    $before = Str::substr($recipe->method, $location - 10, $location + 25);
                    $after = Str::substr($method ?? '', $location - 10, $location + 25);
                    $previousMessage = $e->getMessage();

                    $message = <<<MESSAGE
                    Failed on {$recipe->title} ({$recipe->id}),

                    Before: {$before}
                    After: {$after}

                    Error: {$previousMessage}
                    MESSAGE;

                    throw new Exception($message, previous: $e);
                }

                $progress->advance();
            });
        } catch (Exception $e) {
            dump($e);

            DB::rollBack();
        }

        DB::commit();
    }
}
