<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\ValidArticleHtmlRule;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidArticleHtmlRuleTest extends TestCase
{
    #[Test]
    public function itPassesForPlainHtml(): void
    {
        $this->assertPasses('<p>Hello world</p>');
    }

    #[Test]
    public function itPassesForAnEmptyString(): void
    {
        $this->assertPasses('');
    }

    #[Test]
    public function itAllowsTheArticleHeaderTag(): void
    {
        $this->assertPasses('<article-header>Title</article-header>');
    }

    #[Test]
    public function itAllowsTheArticleImageTag(): void
    {
        $this->assertPasses('<article-image src="image.jpg"></article-image>');
    }

    #[Test]
    public function itAllowsTheArticleIframeTag(): void
    {
        $this->assertPasses('<article-iframe src="https://example.com"></article-iframe>');
    }

    #[Test]
    public function itAllowsTheArticleButtonTag(): void
    {
        $this->assertPasses('<article-button theme="primary" size="md" href="#">Click</article-button>');
    }

    #[Test]
    public function itFailsWhenTheValueContainsARawIframe(): void
    {
        $this->assertFails('<iframe src="x"></iframe>', 'Use <article-iframe> instead of <iframe>');
    }

    #[Test]
    public function itFailsForMismatchedTagCasing(): void
    {
        $this->assertFails(
            '<DIV>text</div>',
            'Mismatched tag casing detected: <DIV>...</div>. Tag names must match exactly.'
        );
    }

    #[Test]
    public function itPassesForMatchingUppercaseTags(): void
    {
        $this->assertPasses('<DIV>text</DIV>');
    }

    #[Test]
    public function itIgnoresUnescapedAmpersands(): void
    {
        $this->assertPasses('<p>Fish & Chips</p>');
    }

    #[Test]
    public function itFailsForGenuinelyInvalidHtml(): void
    {
        $this->assertFails(
            '<p><strong><em>text</p>',
            'Opening and ending tag mismatch: p and em'
        );
    }

    protected function assertPasses(string $value): void
    {
        $failed = false;

        (new ValidArticleHtmlRule())->validate('body', $value, function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    protected function assertFails(string $value, string $expectedMessage): void
    {
        $message = null;

        (new ValidArticleHtmlRule())->validate('body', $value, function (string $fail) use (&$message): void {
            $message = $fail;
        });

        $this->assertSame($expectedMessage, $message);
    }
}
