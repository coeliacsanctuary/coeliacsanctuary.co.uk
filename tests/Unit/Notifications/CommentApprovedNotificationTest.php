<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Notifications\CommentApprovedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class CommentApprovedNotificationTest extends TestCase
{
    protected Comment $comment;

    public function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(5);
        $this->withRecipes(5);
        $this->withCategoriesAndProducts(1, 5);

        $this->comment = $this
            ->build(Comment::class)
            ->on(Blog::query()->first())
            ->create();

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheEmailData(callable $closure): void
    {
        (new AnonymousNotifiable())
            ->route('mail', $this->comment->email)
            ->notify(new CommentApprovedNotification($this->comment));

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            CommentApprovedNotification::class,
            function (CommentApprovedNotification $notification) use ($closure): bool {
                $mail = $notification->toMail(new AnonymousNotifiable());
                $content = $mail->render();

                $closure($this, $mail, $content);

                return true;
            }
        );
    }

    public static function mailDataProvider(): array
    {
        return [
            'has the email key' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($message->data()['key'], $emailContent);
            }],
            'has the comment body' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->comment->comment, $emailContent);
            }],
            'has the comment commentable name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->comment->commentable->title, $emailContent);
            }],
        ];
    }
}
