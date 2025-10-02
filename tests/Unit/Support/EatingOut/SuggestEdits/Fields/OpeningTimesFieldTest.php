<?php

declare(strict_types=1);

namespace Tests\Unit\Support\EatingOut\SuggestEdits\Fields;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Support\EatingOut\SuggestEdits\Fields\OpeningTimesField;
use Database\Seeders\EateryScaffoldingSeeder;
use Tests\TestCase;

class OpeningTimesFieldTest extends TestCase
{
    protected Eatery $eatery;

    protected EateryOpeningTimes $eateryOpeningTimes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);

        $this->openingTimes = $this->create(EateryOpeningTimes::class, [
            'wheretoeat_id' => $this->eatery->id,
        ]);
    }

    #[Test]
    public function itReturnsTheDatabaseValue(): void
    {
        $field = app(OpeningTimesField::class);

        /** @var string $currentValue */
        $currentValue = $field->getCurrentValue($this->eatery);

        $this->assertJson($currentValue);

        $data = json_decode($currentValue, true);

        $this->assertArrayHasKeys(['key', 'label', 'closed', 'start', 'end'], $data[0]);
    }

    #[Test]
    public function itReturnsThePreparedValue(): void
    {
        $field = OpeningTimesField::make(1);

        $this->assertEquals(1, $field->prepare());
    }

    #[Test]
    public function itReturnsTheValueForDisplay(): void
    {
        $data = [[
            'key' => 'monday',
            'label' => 'Monday',
            'start' => [11, 30],
            'end' => [22, 30],
        ]];

        $field = OpeningTimesField::make($data);

        $this->assertEquals(json_encode($data), $field->getSuggestedValue());
    }

    #[Test]
    public function itCanCommitTheSuggestedValue(): void
    {
        $payload = collect(OpeningTimesField::$days)->map(fn (string $day) => [
            'key' => $day,
            'label' => ucfirst($day),
            'start' => [fake()->numberBetween(7, 11), fake()->randomElement([0,15,30,45])],
            'end' => [fake()->numberBetween(16, 23), fake()->randomElement([0,15,30,45])],
        ]);

        $this->openingTimes->delete();

        $field = OpeningTimesField::make(json_encode($payload));

        $this->assertNull($this->eatery->openingTimes);

        $field->commitSuggestedValue($this->eatery);

        $this->eatery->refresh();

        $this->assertNotNull($this->eatery->openingTimes);

        $makeTime = fn (array $time) => Str::padLeft((string) $time[0], 2, '0') . ':' . Str::padLeft((string) $time[1], 2, '0') . ':00';

        foreach ($payload as $data) {
            $this->assertEquals($makeTime($data['start']), $this->eatery->openingTimes->{"{$data['key']}_start"});
            $this->assertEquals($makeTime($data['end']), $this->eatery->openingTimes->{"{$data['key']}_end"});
        }
    }

    #[Test]
    public function itCanCommitTheSuggestedValueAccountingForNullValues(): void
    {
        $payload = collect(OpeningTimesField::$days)->map(fn (string $day) => [
            'key' => $day,
            'label' => ucfirst($day),
            'start' => [fake()->numberBetween(7, 11), fake()->randomElement([0,15,30,45])],
            'end' => [fake()->numberBetween(16, 23), fake()->randomElement([0,15,30,45])],
        ])->toArray();

        // shut sunday
        $payload[6]['start'] = [null, null];
        $payload[6]['end'] = [null, null];

        $this->openingTimes->delete();

        $field = OpeningTimesField::make(json_encode($payload));

        $this->assertNull($this->eatery->openingTimes);

        $field->commitSuggestedValue($this->eatery);

        $this->eatery->refresh();

        $this->assertNotNull($this->eatery->openingTimes);

        $this->assertNull($this->eatery->openingTimes->sunday_start);
        $this->assertNull($this->eatery->openingTimes->sunday_end);
    }
}
