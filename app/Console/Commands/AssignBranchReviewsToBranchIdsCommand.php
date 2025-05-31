<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryReview;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use Illuminate\Console\Command;
use Laravel\Prompts\Progress;

use function Laravel\Prompts\progress;
use function Laravel\Prompts\table;

class AssignBranchReviewsToBranchIdsCommand extends Command
{
    protected $signature = 'one-time:coeliac:assign-branch-reviews-to-branch-ids {--test}';

    protected array $errors = [];
    protected array $warnings = [];
    protected array $success = [];

    public function handle(DetermineNationwideBranchFromNamePipeline $pipeline): void
    {
        $branches = EateryReview::query()
            ->whereNull('nationwide_branch_id')
            ->whereNotNull('branch_name')
            ->whereNot('branch_name', '')
            ->with(['eatery', 'eatery.nationwideBranches'])
            ->get();

        progress(
            'Processing Reviewed Nationwide Branches',
            $branches,
            function (EateryReview $review, Progress $progress) use ($pipeline): void {
                if ( ! $review->eatery) {
                    $this->errors[] = ("Eatery for review {$review->id} not found");

                    return;
                }

                $progress->label("Currently processing review for {$review->eatery->name} with branch name of {$review->branch_name}");

                $branch = $pipeline->run(
                    $review->eatery,
                    null,
                    $review->branch_name,
                );

                if ( ! $branch) {
                    $this->warnings[] = ("Branch for review {$review->id} not found - '{$review->branch_name}' of eatery {$review->eatery->name}");

                    return;
                }

                $progress->hint("Found branch {$branch->id} - {$branch->full_name}");
                $this->success[] = "Set review '{$review->branch_name}' of eatery {$review->eatery->name} to branch {$branch->id} - {$branch->full_name}";

                if ( ! $this->option('test')) {
                    $review->updateQuietly(['nationwide_branch_id' => $branch->id]);
                }
            }
        );

        table(['Successful Updates - ' . count($this->success)], array_map(fn ($row) => [$row], $this->success));
        table(['Warnings - ' . count($this->warnings)], array_map(fn ($row) => [$row], $this->warnings));
        table(['Failures - ' . count($this->errors)], array_map(fn ($row) => [$row], $this->errors));
    }
}
