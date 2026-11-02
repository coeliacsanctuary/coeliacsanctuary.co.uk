<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables;

use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Mailables\CommentApprovedMailable;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use Tests\TestCase;

class CommentApprovedMailableTest extends TestCase
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
            CommentApprovedMailable::make($this->build(Comment::class)->on(Blog::query()->first())->create(), 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        /** @var Comment $comment */
        $comment = $this->build(Comment::class)->on(Blog::query()->first())->create();

        $mailable = CommentApprovedMailable::make($comment, 'foo');

        $this->assertEquals("Your comment on {$comment->commentable->title} on Coeliac Sanctuary has been approved!", $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = CommentApprovedMailable::make($this->build(Comment::class)->on(Blog::query()->first())->create(), 'foo');

        $this->assertEquals('mailables.mjml.comment-approved', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        /** @var Comment $comment */
        $comment = $this->build(Comment::class)->on(Blog::query()->first())->create();

        $data = [
            'comment' => fn ($assertionReview) => $this->assertTrue($comment->is($assertionReview)),
            'email' => fn ($email) => $this->assertEquals($comment->email, $email),
            'reason' => fn ($reason) => $this->assertEquals('to let you know your comment on Coeliac Sanctuary has been approved.', $reason),
        ];

        $mailable = CommentApprovedMailable::make($comment, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
