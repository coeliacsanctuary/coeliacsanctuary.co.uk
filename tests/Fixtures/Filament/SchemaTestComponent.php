<?php

declare(strict_types=1);

namespace Tests\Fixtures\Filament;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class SchemaTestComponent extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<int, mixed> */
    public static array $schemaComponents = [];

    public static string $operation = 'create';

    public static ?Model $schemaRecord = null;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(static::$schemaRecord?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(static::$schemaComponents)
            ->operation(static::$operation)
            ->record(static::$schemaRecord)
            ->statePath('data');
    }

    public function render(): View
    {
        return view()->file(__DIR__ . '/schema-test-component.blade.php');
    }
}
