<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Contracts\Comments\HasComments;
use App\Models\Comments\Comment;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'comment' => $this->faker->paragraph,
        ];
    }

    public function on(HasComments $commentable)
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => $commentable::class,
            'commentable_id' => $commentable->id,
        ]);
    }

    public function approved(): self
    {
        return $this->state(fn () => ['approved' => true]);
    }
}
