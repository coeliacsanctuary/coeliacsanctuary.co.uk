<?php

declare(strict_types=1);

namespace Feature\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits;

use App\Actions\EatingOut\StoreSuggestedEditAction;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
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
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.suggest-edit.store', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsAValidationErrorWithAMissingOrInvalidField(): void
    {
        $this->makeRequest(field: null)->assertJsonValidationErrorFor('field');

        $this->makeRequest(field: 'foo')->assertJsonValidationErrorFor('field');
    }

    #[Test]
    public function itReturnsAValidationErrorWithoutAValue(): void
    {
        $this->makeRequest(value: null)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForAnAddress(): void
    {
        $this->makeRequest(field: 'address', value: null)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'address', value: 123)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'address', value: true)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForAnCuisine(): void
    {
        $this->makeRequest(field: 'cuisine', value: null)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'cuisine', value: 'foo')->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'cuisine', value: true)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'cuisine', value: 123)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesThatIsntAnArray(): void
    {
        $this->makeRequest(field: 'features', value: null)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'features', value: 'foo')->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'features', value: true)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'features', value: 123)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesWithAnInvalidKey(): void
    {
        $this->makeRequest(field: 'features', value: [['key' => null]])->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'features', value: [['key' => 'foo']])->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'features', value: [['key' => true]])->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'features', value: [['key' => 123]])->assertJsonValidationErrorFor('value.0.key');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesWithAnInvalidLabel(): void
    {
        $this->makeRequest(field: 'features', value: [['label' => null]])->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'features', value: [['label' => true]])->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'features', value: [['label' => 123]])->assertJsonValidationErrorFor('value.0.label');
    }

    #[Test]
    public function itErrorsWhenSubmittingFeaturesWithAnInvalidSelectedToggle(): void
    {
        $this->makeRequest(field: 'features', value: [['selected' => null]])->assertJsonValidationErrorFor('value.0.selected');

        $this->makeRequest(field: 'features', value: [['selected' => 'foo']])->assertJsonValidationErrorFor('value.0.selected');

        $this->makeRequest(field: 'features', value: [['selected' => 123]])->assertJsonValidationErrorFor('value.0.selected');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForTheMenuLink(): void
    {
        $this->makeRequest(field: 'gf_menu_link', value: null)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'gf_menu_link', value: 123)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'gf_menu_link', value: true)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'gf_menu_link', value: 'foobar')->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForTheInfoField(): void
    {
        $this->makeRequest(field: 'gf_menu_link', value: null)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesThatIsntAnArray(): void
    {
        $this->makeRequest(field: 'opening_times', value: null)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: 'foo')->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: true)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: 123)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsIfTheValueArrayIsLessThan7Elements(): void
    {
        $this->makeRequest(field: 'opening_times', value: [1, 2, 3, 4, 5, 6])->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'opening_times', value: [1, 2, 3, 4, 5, 6, 7, 8])->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidKey(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['key' => null]])->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'opening_times', value: [['key' => 'foo']])->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'opening_times', value: [['key' => true]])->assertJsonValidationErrorFor('value.0.key');

        $this->makeRequest(field: 'opening_times', value: [['key' => 123]])->assertJsonValidationErrorFor('value.0.key');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidLabel(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['label' => null]])->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'opening_times', value: [['label' => 'foo']])->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'opening_times', value: [['label' => true]])->assertJsonValidationErrorFor('value.0.label');

        $this->makeRequest(field: 'opening_times', value: [['label' => 123]])->assertJsonValidationErrorFor('value.0.label');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidStartTime(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['start' => null]])->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => 'foo']])->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => true]])->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => 123]])->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => [1]]])->assertJsonValidationErrorFor('value.0.start');

        $this->makeRequest(field: 'opening_times', value: [['start' => [1, 2, 3]]])->assertJsonValidationErrorFor('value.0.start');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidStartHour(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['start' => ['foo']]])->assertJsonValidationErrorFor('value.0.start.0');

        $this->makeRequest(field: 'opening_times', value: [['start' => [true]]])->assertJsonValidationErrorFor('value.0.start.0');

        $this->makeRequest(field: 'opening_times', value: [['start' => [-1]]])->assertJsonValidationErrorFor('value.0.start.0');

        $this->makeRequest(field: 'opening_times', value: [['start' => [24]]])->assertJsonValidationErrorFor('value.0.start.0');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidStartMinutes(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['start' => [0, 'foo']]])->assertJsonValidationErrorFor('value.0.start.1');

        $this->makeRequest(field: 'opening_times', value: [['start' => [0, true]]])->assertJsonValidationErrorFor('value.0.start.1');

        $this->makeRequest(field: 'opening_times', value: [['start' => [0, -1]]])->assertJsonValidationErrorFor('value.0.start.1');

        $this->makeRequest(field: 'opening_times', value: [['start' => [0, 60]]])->assertJsonValidationErrorFor('value.0.start.1');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidEndTime(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['end' => null]])->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => 'foo']])->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => true]])->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => 123]])->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => [1]]])->assertJsonValidationErrorFor('value.0.end');

        $this->makeRequest(field: 'opening_times', value: [['end' => [1, 2, 3]]])->assertJsonValidationErrorFor('value.0.end');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidEndHour(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['end' => ['foo']]])->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(field: 'opening_times', value: [['end' => [true]]])->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(field: 'opening_times', value: [['end' => [-1]]])->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(field: 'opening_times', value: [['end' => [24]]])->assertJsonValidationErrorFor('value.0.end.0');

        $this->makeRequest(
            field: 'opening_times',
            value: [
                [
                    'start' => [12],
                    'end' => [11],
                ],
            ],
        )->assertJsonValidationErrorFor('value.0.end.0');
    }

    #[Test]
    public function itErrorsWhenSubmittingOpeningTimesWithAnInvalidEndMinutes(): void
    {
        $this->makeRequest(field: 'opening_times', value: [['end' => [0, 'foo']]])->assertJsonValidationErrorFor('value.0.end.1');

        $this->makeRequest(field: 'opening_times', value: [['end' => [0, true]]])->assertJsonValidationErrorFor('value.0.end.1');

        $this->makeRequest(field: 'opening_times', value: [['end' => [0, -1]]])->assertJsonValidationErrorFor('value.0.end.1');

        $this->makeRequest(field: 'opening_times', value: [['end' => [0, 60]]])->assertJsonValidationErrorFor('value.0.end.1');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForThePhoneField(): void
    {
        $this->makeRequest(field: 'phone', value: null)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForAnCVenueType(): void
    {
        $this->makeRequest(field: 'venue_type', value: null)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'venue_type', value: 'foo')->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'venue_type', value: true)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'venue_type', value: 123)->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itErrorWhenSubmittingAnInvalidValueForTheWebsite(): void
    {
        $this->makeRequest(field: 'website', value: null)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'website', value: 123)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'website', value: true)->assertJsonValidationErrorFor('value');

        $this->makeRequest(field: 'website', value: 'foobar')->assertJsonValidationErrorFor('value');
    }

    #[Test]
    public function itReturnsOkForAValidRequest(): void
    {
        $this->makeRequest()->assertNoContent();
    }

    #[Test]
    public function itCallsTheStoreSuggestedEditAction(): void
    {
        $this->expectAction(StoreSuggestedEditAction::class, [Eatery::class, 'address', 'foobar']);

        $this->makeRequest('address', 'foobar')->assertNoContent();
    }

    protected function makeRequest($field = 'address', $value = 'foo', string $source = 'foo'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.details.suggest-edit.store', ['eatery' => $this->eatery]),
            ['field' => $field, 'value' => $value],
            ['x-coeliac-source' => $source],
        );
    }
}
