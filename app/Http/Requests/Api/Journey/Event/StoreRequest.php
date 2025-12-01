<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Journey\Event;

use App\DataObjects\Journey\QueuedEventData;
use App\Enums\Journey\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'event_type' => ['required', Rule::enum(EventType::class)],
            'event_identifier' => ['required', 'string'],
            'data' => ['array'],
            'sensitive' => ['boolean'],
        ];
    }

    public function toData(): QueuedEventData
    {
        /** @var array{session_id: string, path: string} $token */
        $token = Crypt::decrypt($this->string('token')->toString());

        return new QueuedEventData(
            sessionId: $token['session_id'],
            path: $token['path'],
            eventType: $this->enum('event_type', EventType::class),
            eventIdentifier: $this->string('event_identifier')->toString(),
            data: $this->array('data'),
            sensitive: $this->boolean('sensitive'),
            timestamp: time(),
        );
    }
}
