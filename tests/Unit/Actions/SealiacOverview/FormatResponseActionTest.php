<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\SealiacOverview;

use App\Actions\SealiacOverview\FormatResponseAction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormatResponseActionTest extends TestCase
{
    #[Test]
    public function itWrapsTheResponseInQuoteElements(): void
    {
        $response = "foo\nbar\nbaz";

        $formattedResponse = app(FormatResponseAction::class)->handle($response);

        $expectedString = "<p><span class=\"quote-elem close\"><span>&rdquo;</span></span><span class=\"quote-elem open\"><span>&ldquo;</span></span>foo<br />bar<br />baz</p>\n";

        $this->assertEquals($expectedString, $formattedResponse->toString());
    }
}
