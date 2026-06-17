<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\Body;
use App\Rules\ValidArticleHtmlRule;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BodyTest extends TestCase
{
    #[Test]
    public function itDefaultsToTwentyFiveRows(): void
    {
        $this->assertSame(25, Body::make('body')->getRows());
    }

    #[Test]
    public function itAllowsOverridingRows(): void
    {
        $this->assertSame(15, Body::make('body')->rows(15)->getRows());
    }

    #[Test]
    public function itHasTheToolbarEnabledByDefault(): void
    {
        $this->assertTrue(Body::make('body')->hasToolbar());
    }

    #[Test]
    public function itCanDisableTheToolbar(): void
    {
        $this->assertFalse(Body::make('body')->toolbar(false)->hasToolbar());
    }

    #[Test]
    public function itCanReEnableTheToolbar(): void
    {
        $this->assertTrue(Body::make('body')->toolbar(false)->toolbar()->hasToolbar());
    }

    #[Test]
    public function itUsesTheCustomBodyView(): void
    {
        $this->assertSame('filament.forms.components.body', Body::make('body')->getView());
    }

    #[Test]
    public function itDoesNotAddTheValidationRuleByDefault(): void
    {
        $rules = collect(Body::make('body')->getValidationRules());

        $this->assertFalse($rules->contains(fn ($rule) => $rule instanceof ValidArticleHtmlRule));
    }

    #[Test]
    public function itAddsTheValidArticleHtmlRuleWhenValidHtmlIsCalled(): void
    {
        $rules = collect(Body::make('body')->validHtml()->getValidationRules());

        $this->assertTrue($rules->contains(fn ($rule) => $rule instanceof ValidArticleHtmlRule));
    }
}
