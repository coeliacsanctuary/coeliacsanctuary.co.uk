<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @mixin TestCase */
trait DisplaysDatesTestTrait
{
    /** @var callable(array): Model */
    protected $displaysDatesFactoryClosure;

    /** @param callable(array $parameters): Model $factory */
    protected function setUpDisplaysDatesTest(callable $factory): void
    {
        $this->displaysDatesFactoryClosure = $factory;
    }

    private function displaysDatesFactory(array $params = []): Model
    {
        return call_user_func($this->displaysDatesFactoryClosure, $params);
    }

    #[Test]
    public function itReturnsNullLastUpdatedWhenTheModelHasNeverBeenEdited(): void
    {
        $this->assertNull($this->displaysDatesFactory()->lastUpdated);
    }

    #[Test]
    public function itReturnsTheLastUpdatedDateWhenTheModelHasBeenEdited(): void
    {
        $model = $this->displaysDatesFactory();

        $model->update(['updated_at' => Carbon::now()->addYear()]);

        $this->assertNotNull($model->fresh()->lastUpdated);
    }

    #[Test]
    public function itFormatsAnOlderLastUpdatedDateRatherThanUsingDiffForHumans(): void
    {
        $model = $this->displaysDatesFactory(['created_at' => Carbon::now()->subYears(2)]);

        $model->update(['updated_at' => Carbon::now()->subYear()]);

        $this->assertSame(
            Carbon::now()->subYear()->format('jS F Y'),
            $model->fresh()->lastUpdated
        );
    }
}
