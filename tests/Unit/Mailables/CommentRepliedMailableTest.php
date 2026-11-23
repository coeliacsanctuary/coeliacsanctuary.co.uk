<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables;

use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Mailables\CommentRepliedMailable;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReply;
use Tests\TestCase;

class CommentRepliedMailableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(5);
    }

    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            CommentRepliedMailable::make($this->build(CommentReply::class)->on(Blog::query()->first())->create(), 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        /** @var Comment $comment */
        $comment = $this->build(CommentReply::class)->on(Blog::query()->first())->create();

        $mailable = CommentRepliedMailable::make($comment, 'foo');

        $this->assertEquals("Your comment on {$comment->comment->
       commentable->title} on Coeliac Sanctuary has been replied to!", $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = CommentRepliedMailable::make($this->build(CommentReply::class)->on(Blog::query()->first())->create(), 'foo');

        $this->assertEquals('mailables.mjml.comment-replied', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        /** @var CommentReply $comment */
        $comment = $this->build(CommentReply::class)->on(Blog::query()->first())->create();

        $data = [
            'reply' => fn ($assertionReview) => $this->assertTrue($comment->is($assertionReview)),
            'email' => fn ($email) => $this->assertEquals($comment->comment->email, $email),
            'reason' => fn ($reason) => $this->assertEquals('to let you know your comment on Coeliac Sanctuary has been replied to.', $reason),
        ];

        $mailable = CommentRepliedMailable::make($comment, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
