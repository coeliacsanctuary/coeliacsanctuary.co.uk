<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\EatingOut;

use PHPUnit\Framework\Attributes\Test;
use App\Http\Requests\EatingOut\SuggestEditRequest;
use App\Models\EatingOut\EaterySuggestedEdit;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\In;
use Tests\TestCase;

class SuggestedEditRequestTest extends TestCase
{
    #[Test]
    public function itReturnsTheAvailableEditableFieldsAsAnInRule(): void
    {
        $rules = new SuggestEditRequest()->rules();

        $this->assertArrayHasInstanceOf(In::class, $rules['field']);

        /** @var In $inRule */
        $inRule = Arr::first($rules['field'], fn ($value): bool => $value instanceof In);

        $expectedKeys = collect(array_keys(EaterySuggestedEdit::processorMaps()))
            ->map(fn ($key): string => '"' . $key . '"')
            ->join(',');

        $this->assertEquals("in:{$expectedKeys}", $inRule->__toString());
    }

    #[Test]
    public function itReturnsTheValidationRulesFromTheEditableInstance(): void
    {
        $mock = $this->partialMock(SuggestEditRequest::class);

        $class = new class () {
            public static function validationRules(): array
            {
                return ['foo', 'bar'];
            }
        };

        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('resolveProcessorClass')
            ->andReturn($class::class);

        $rules = $mock->rules();

        $this->assertArrayHasKey('value', $rules);
        $this->assertEquals(['foo', 'bar'], $rules['value']);
    }

    #[Test]
    public function itReturnsTheValidationRulesFromTheEditableInstanceAsARuleEntityForComplexRules(): void
    {
        $mock = $this->partialMock(SuggestEditRequest::class);

        $class = new class () {
            public static function validationRules(): array
            {
                return [
                    'value' => ['required', 'array'],
                    'value.foo' => ['required'],
                ];
            }
        };

        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('resolveProcessorClass')
            ->andReturn($class::class);

        $rules = $mock->rules();

        $this->assertArrayHasKey('value', $rules);
        $this->assertArrayHasKey('value.foo', $rules);
        $this->assertEquals(['required', 'array'], $rules['value']);
        $this->assertEquals(['required'], $rules['value.foo']);
    }
}
