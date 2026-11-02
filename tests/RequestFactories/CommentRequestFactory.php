<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class CommentRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'module' => 'blog',
            'id' => 1,
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'comment' => $this->faker->paragraph(),
        ];
    }
}
