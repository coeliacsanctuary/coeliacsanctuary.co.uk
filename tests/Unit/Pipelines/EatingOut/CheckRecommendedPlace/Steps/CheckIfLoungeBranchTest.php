<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\CheckRecommendedPlace\Steps;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\AbstractStepAction;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\CheckIfLoungeBranch;

class CheckIfLoungeBranchTest extends StepTestCase
{
    protected Eatery $eatery;

    protected NationwideBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eatery = $this->create(Eatery::class, ['name' => 'The Lounges']);
        $this->branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
            'name' => 'Navio Lounge',
        ]);
    }

    #[Test]
    #[DataProvider('validValues')]
    public function itMarksItAsFoundIfSearchingForValidData(string $name): void
    {
        $data = $this->makeData($name);

        $this->getStep()->handle($data, fn () => null);

        $this->assertTrue($data->found);
        $this->assertEquals("It looks like you're recommending The Lounges, did you know we've already got those listed on our Nationwide page?", $data->reason);
        $this->assertEquals(route('eating-out.nationwide.show', ['eatery' => $this->eatery->slug]), $data->url);
        $this->assertEquals('Check out The Lounges', $data->label);
    }

    #[Test]
    public function itReturnsNotFoundForALoungeBranchThatDoesntExist(): void
    {
        $data = $this->makeData('Capello Lounge');

        $this->getStep()->handle($data, fn () => null);

        $this->assertFalse($data->found);
    }

    #[Test]
    public function itFindsALoungeBranch(): void
    {
        $data = $this->makeData('navio lounge');

        $this->getStep()->handle($data, fn () => null);

        $this->assertTrue($data->found);
        $this->assertEquals("It looks like you're recommending {$this->branch->name}, {$this->branch->full_location}, part of The Lounge's chain, but we've already got them listed!", $data->reason);
        $this->assertEquals($this->branch->link(), $data->url);
        $this->assertEquals('Check out Navio Lounge', $data->label);
    }

    public static function validValues(): array
    {
        return [
            'the lounge' => ['the lounge'],
            'the lounges' => ['the lounges'],
            'The Lounge' => ['The Lounge'],
            'The Lounges' => ['The Lounges'],
        ];
    }

    protected function getStep(): AbstractStepAction
    {
        return app(CheckIfLoungeBranch::class);
    }
}
