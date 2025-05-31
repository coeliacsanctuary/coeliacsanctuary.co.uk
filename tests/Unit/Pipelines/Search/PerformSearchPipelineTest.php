<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\Search;

use App\DataObjects\Search\SearchParameters;
use App\Pipelines\Search\PerformSearchPipeline;
use App\Pipelines\Search\Steps\CombineResults;
use App\Pipelines\Search\Steps\HydratePage;
use App\Pipelines\Search\Steps\PaginateResults;
use App\Pipelines\Search\Steps\PrepareResource;
use App\Pipelines\Search\Steps\SearchBlogs;
use App\Pipelines\Search\Steps\SearchEateries;
use App\Pipelines\Search\Steps\SearchRecipes;
use App\Pipelines\Search\Steps\SearchShop;
use App\Pipelines\Search\Steps\SortResults;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerformSearchPipelineTest extends TestCase
{
    #[Test]
    public function itCallsThePipelineSteps(): void
    {
        $this->expectPipelineToExecute(SearchBlogs::class);
        $this->expectPipelineToExecute(SearchRecipes::class);
        $this->expectPipelineToExecute(SearchEateries::class);
        $this->expectPipelineToExecute(SearchShop::class);
        $this->expectPipelineToExecute(CombineResults::class);
        $this->expectPipelineToExecute(SortResults::class);
        $this->expectPipelineToExecute(PaginateResults::class);
        $this->expectPipelineToExecute(HydratePage::class);
        $this->expectPipelineToExecute(PrepareResource::class);

        $this->runPipeline(PerformSearchPipeline::class, new SearchParameters('foo'));
    }
}
