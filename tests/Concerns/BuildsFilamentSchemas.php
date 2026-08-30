<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\Fixtures\Filament\SchemaTestComponent;
use Tests\TestCase;

/** @mixin TestCase */
trait BuildsFilamentSchemas
{
    /** @param array<int, mixed> $components */
    protected function mountSchema(array $components, string $operation = 'create', ?Model $record = null): Testable
    {
        SchemaTestComponent::$schemaComponents = $components;
        SchemaTestComponent::$operation = $operation;
        SchemaTestComponent::$schemaRecord = $record;

        return Livewire::test(SchemaTestComponent::class);
    }

    /** @param array<int, mixed> $components */
    protected function mountedComponent(string $key, array $components, string $operation = 'create', ?Model $record = null): mixed
    {
        return $this->mountSchema($components, $operation, $record)
            ->instance()
            ->getSchema('form')
            ->getFlatComponents(withHidden: true)[$key] ?? null;
    }

    /** @param array<int, mixed> $components */
    protected function mountedRootComponent(array $components, string $operation = 'create', ?Model $record = null): mixed
    {
        return $this->mountSchema($components, $operation, $record)
            ->instance()
            ->getSchema('form')
            ->getComponents(withHidden: true)[0];
    }
}
