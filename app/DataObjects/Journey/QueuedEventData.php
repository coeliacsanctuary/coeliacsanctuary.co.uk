<?php

declare(strict_types=1);

namespace App\DataObjects\Journey;

use App\Enums\Journey\EventType;

final readonly class QueuedEventData
{
    public function __construct(
        public string $sessionId,
        public string $path,
        public EventType $eventType,
        public string $eventIdentifier,
        public array $data,
        public bool $sensitive,
        public int $timestamp,
    ) {
        //
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
