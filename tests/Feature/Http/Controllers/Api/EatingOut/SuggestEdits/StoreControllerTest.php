<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\SuggestEdits;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\StoreSuggestedEditAction;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itReturnsAValidationErrorWithAMissingOrInvalidField(): void
    {
        $this->makeRequest(field: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('field');

        $this->makeRequest(field: 'foo')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('field');
    }

    #[Test]
    public function itReturnsAValidationErrorWithoutAValue(): void
    {
        $this->makeRequest(value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForAnAddress(): void
    {
        $this->makeRequest(field: 'address', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'address', value: 123)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'address', value: true)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForAnCuisine(): void
    {
        $this->makeRequest(field: 'cuisine', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'cuisine', value: 'foo')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'cuisine', value: true)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'cuisine', value: 123)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesThatIsntAnArray(): void
    {
        $this->makeRequest(field: 'features', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'features', value: 'foo')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'features', value: true)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'features', value: 123)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesWithAnInvalidKey(): void
    {
        $this->makeRequest(field: 'features', value: [['key' => null]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'features', value: [['key' => 'foo']])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'features', value: [['key' => true]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'features', value: [['key' => 123]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesWithAnInvalidLabel(): void
    {
        $this->makeRequest(field: 'features', value: [['label' => null]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'features', value: [['label' => true]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'features', value: [['label' => 123]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.label');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesWithAnInvalidSelectedToggle(): void
    {
        $this->makeRequest(field: 'features', value: [['selected' => null]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.selected');

        $this->makeRequest(field: 'features', value: [['selected' => 'foo']])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.selected');

        $this->makeRequest(field: 'features', value: [['selected' => 123]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.selected');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForTheMenuLink(): void
    {
        $this->makeRequest(field: 'gf_menu_link', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'gf_menu_link', value: 123)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'gf_menu_link', value: true)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'gf_menu_link', value: 'foobar')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForTheInfoField(): void
    {
        $this->makeRequest(field: 'gf_menu_link', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesThatIsntAnArray(): void
    {
        $this->makeRequest(field: 'opening_times', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: 'foo')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: true)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: 123)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsIfTheValueArrayIsLessThan7Elements(): void
    {
        $this->makeRequest(field: 'opening_times', value: [1, 2, 3, 4, 5, 6])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: [1, 2, 3, 4, 5, 6, 7, 8])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidKey(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['key' => null]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'opening_times', value: [['key' => 'foo']])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'opening_times', value: [['key' => true]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'opening_times', value: [['key' => 123]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.key');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidLabel(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['label' => null]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'opening_times', value: [['label' => 'foo']])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'opening_times', value: [['label' => true]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'opening_times', value: [['label' => 123]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.label');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidStartTime(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['start' => null]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => 'foo']])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => true]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => 123]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => [1]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => [1, 2, 3]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidStartHour(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['start' => ['foo']]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.0');

        $this->makeRequest(field: 'opening_times', value: [['start' => [true]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.0');

        $this->makeRequest(field: 'opening_times', value: [['start' => [-1]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.0');

        $this->makeRequest(field: 'opening_times', value: [['start' => [24]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.0');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidStartMinutes(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['start' => [0, 'foo']]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.1');

        $this->makeRequest(field: 'opening_times', value: [['start' => [0, true]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.1');

        $this->makeRequest(field: 'opening_times', value: [['start' => [0, -1]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.1');

        $this->makeRequest(field: 'opening_times', value: [['start' => [0, 60]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.start.1');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidEndTime(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['end' => null]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => 'foo']])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => true]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => 123]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => [1]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => [1, 2, 3]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidEndHour(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['end' => ['foo']]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(field: 'opening_times', value: [['end' => [true]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(field: 'opening_times', value: [['end' => [-1]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(field: 'opening_times', value: [['end' => [24]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(
            field: 'opening_times',
            value: [
                [
                    'start' => [12],
                    'end' => [11],
                ],
            ],
        )
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.0');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidEndMinutes(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['end' => [0, 'foo']]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.1');

        $this->makeRequest(field: 'opening_times', value: [['end' => [0, true]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.1');

        $this->makeRequest(field: 'opening_times', value: [['end' => [0, -1]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.1');

        $this->makeRequest(field: 'opening_times', value: [['end' => [0, 60]]])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value.0.end.1');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForThePhoneField(): void
    {
        $this->makeRequest(field: 'phone', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForAnCVenueType(): void
    {
        $this->makeRequest(field: 'venue_type', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'venue_type', value: 'foo')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'venue_type', value: true)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'venue_type', value: 123)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForTheWebsite(): void
    {
        $this->makeRequest(field: 'website', value: null)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'website', value: 123)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'website', value: true)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'website', value: 'foobar')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itReturnsOkForAValidRequest(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itCallsTheStoreSuggestedEditAction(): void
    {
        $this->expectAction(StoreSuggestedEditAction::class, [Eatery::class, 'address', 'foobar']);

        $this->makeRequest('address', 'foobar')->assertOk();
    }

    protected function makeRequest($field = 'address', $value = 'foo'): TestResponse
    {
        return $this->postJson(
            route('api.wheretoeat.suggest-edit.store', ['eatery' => $this->eatery->id]),
            [
                'field' => $field,
                'value' => $value,
            ],
        );
    }
}
