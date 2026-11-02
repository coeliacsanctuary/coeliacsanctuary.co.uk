<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\CheckRecommendedPlace\Steps;

use PHPUnit\Framework\Attributes\Test;
use App\DataObjects\EatingOut\RecommendAPlaceExistsCheckData;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\AbstractStepAction;
use Tests\TestCase;

abstract class StepTestCase extends TestCase
{
    protected function makeData(?string $name = null, ?string $location = null, bool $found = false): RecommendAPlaceExistsCheckData
    {
        return new RecommendAPlaceExistsCheckData($name, $location, $found);
    }

    #[Test]
    public function itSkipsTheCheckIfTheRecommendationHasBeenFound(): void
    {
        $data = $this->makeData(found: true);

        $this->mock($this->getStep()::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('handle')
            ->getMock()
            ->shouldNotReceive('check');

        $this->getStep()->handle($data, fn (): null => null);
    }

    abstract protected function getStep(): AbstractStepAction;
}
