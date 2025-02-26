<?php

declare(strict_types=1);

namespace App\Nova\FieldOverrides;

use Closure;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 *
 * @method static static make(string $name, Closure $lines)
 */
class Stack extends \Laravel\Nova\Fields\Stack
{
    protected ?Closure $loadClasses = null;

    protected ?Closure $unpreparedLines = null;

    protected ?Closure $processUsingCallback = null;

    public function __construct($name, Closure $lines)
    {
        parent::__construct($name);

        $this->unpreparedLines = $lines;
    }

    public function prepareLines($resource, $attribute = null): void
    {
        $this->processLines($resource);

        $request = app(NovaRequest::class);

        $this->lines = $this->lines
            ->map(fn (string | Line $line) => $line instanceof Line ? $line : Line::make('', fn () => $line))
            ->filter(function (Line $field) use ($request, $resource) {
                if ($request->isResourceIndexRequest()) {
                    return $field->isShownOnIndex($request, $resource);
                }

                return $field->isShownOnDetail($request, $resource);
            })->values()->each->resolveForDisplay($resource, $attribute);
    }

    public function processUsing(Closure $callback): self
    {
        $this->processUsingCallback = $callback;

        return $this;
    }

    protected function processLines(mixed $resource): void
    {
        $this->lines = collect(call_user_func($this->unpreparedLines, $resource))
            ->when($this->processUsingCallback, fn (Collection $collection) => call_user_func($this->processUsingCallback, $collection))
            ->flatten();
    }

    public function withClasses(Closure $callable): self
    {
        $this->loadClasses = $callable;

        return $this;
    }
}
