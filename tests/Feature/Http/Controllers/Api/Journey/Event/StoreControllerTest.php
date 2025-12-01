<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Journey\Event;

use App\DataObjects\Journey\QueuedEventData;
use App\Enums\Journey\EventType;
use App\Jobs\Journey\LogPageEventJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    #[Test]
    public function itErrorsWithoutAJourneyId(): void
    {
        $this->makeRequest(['journey_id' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['journey_id']);
    }

    #[Test]
    public function itErrorsWithAnInvalidJourneyId(): void
    {
        $this->makeRequest(['journey_id' => 'foo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['journey_id']);

        $this->makeRequest(['journey_id' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['journey_id']);

        $this->makeRequest(['journey_id' => 123])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['journey_id']);
    }

    #[Test]
    public function itErrorsWithoutAPageViewIdId(): void
    {
        $this->makeRequest(['page_view_id' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page_view_id']);
    }

    #[Test]
    public function itErrorsWithAnInvalidPageViewIdId(): void
    {
        $this->makeRequest(['page_view_id' => 'foo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page_view_id']);

        $this->makeRequest(['page_view_id' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page_view_id']);

        $this->makeRequest(['page_view_id' => 123])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page_view_id']);
    }

    #[Test]
    public function itErrorsWithoutAEventType(): void
    {
        $this->makeRequest(['event_type' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);
    }

    #[Test]
    public function itErrorsWithAnInvalidEventType(): void
    {
        $this->makeRequest(['event_type' => 'foo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);

        $this->makeRequest(['event_type' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);

        $this->makeRequest(['event_type' => 123])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);
    }

    #[Test]
    public function itErrorsWithoutAEventIdentifier(): void
    {
        $this->makeRequest(['event_identifier' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_identifier']);
    }

    #[Test]
    public function itErrorsWithAnInvalidEventIdentifier(): void
    {
        $this->makeRequest(['event_identifier' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_identifier']);

        $this->makeRequest(['event_identifier' => 123])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_identifier']);
    }

    #[Test]
    public function itErrorsWithAnInvalidData(): void
    {
        $this->makeRequest(['data' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data']);

        $this->makeRequest(['data' => 123])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data']);

        $this->makeRequest(['data' => 'foo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data']);
    }

    #[Test]
    public function itDispatchesTheLogPageEventJob(): void
    {
        $requestData = [
            'journey_id' => Str::uuid()->toString(),
            'page_view_id' => Str::uuid()->toString(),
            'event_type' => EventType::CLICKED->value,
            'event_identifier' => 'foo',
            'data' => ['foo' => 'bar'],
        ];

        $this->makeRequest($requestData);

        Bus::assertDispatched(LogPageEventJob::class, function (LogPageEventJob $event) use ($requestData) {
            /** @var QueuedEventData $data */
            $data = invade($event)->data;

            $this->assertEquals($requestData['journey_id'], $data->journeyId);
            $this->assertEquals($requestData['page_view_id'], $data->pageViewId);
            $this->assertEquals(EventType::from($requestData['event_type']), $data->eventType);
            $this->assertEquals($requestData['event_identifier'], $data->eventIdentifier);
            $this->assertEquals($requestData['data'], $data->data);

            return true;
        });
    }

    #[Test]
    public function itReturnsNoContent(): void
    {
        $this->makeRequest()->assertNoContent();
    }

    protected function makeRequest(array $data = []): TestResponse
    {
        return $this->postJson(route('api.journey.event.store'), array_merge([
            'journey_id' => Str::uuid()->toString(),
            'page_view_id' => Str::uuid()->toString(),
            'event_type' => EventType::CLICKED->value,
            'event_identifier' => 'foo',
            'data' => [],
        ], $data));
    }
}
