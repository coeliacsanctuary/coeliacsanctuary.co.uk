<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\NotificationEmail;
use App\Models\Shop\ShopCustomer;
use Illuminate\Support\Carbon;

class NotificationEmailFactory extends Factory
{
    protected $model = NotificationEmail::class;

    public function definition(): array
    {
        return [
            'user_id' => static::factoryForModel(ShopCustomer::class),
            'email_address' => $this->faker->unique()->safeEmail(),
            'template' => 'mailables.mjml.comment-approved',
            'data' => [
                'date' => now()->toString(),
                'comment' => static::factoryForModel(Comment::class)->on(static::factoryForModel(Blog::class)->create())->approved()->create(),
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
