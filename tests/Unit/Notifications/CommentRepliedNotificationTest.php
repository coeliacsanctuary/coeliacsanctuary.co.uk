<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Models\Blogs\Blog;
use App\Models\Comments\CommentReply;
use App\Notifications\CommentRepliedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class CommentRepliedNotificationTest extends TestCase
{
    protected CommentReply $reply;

    public function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(5);
        $this->withRecipes(5);
        $this->withCategoriesAndProducts(1, 5);

        $this->reply = $this
            ->build(CommentReply::class)
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
            ->route('mail', $this->reply->comment->email)
            ->notify(new CommentRepliedNotification($this->reply));

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            CommentRepliedNotification::class,
            function (CommentRepliedNotification $notification) use ($closure): bool {
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
            'has the comment reply' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->reply->comment_reply, $emailContent);
            }],
            'has the comment body' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->reply->comment->comment, $emailContent);
            }],
            'has the comment commentable name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->reply->comment->commentable->title, $emailContent);
            }],
        ];
    }
}
