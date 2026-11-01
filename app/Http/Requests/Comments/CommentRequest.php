<?php

declare(strict_types=1);

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\Attributes\StopOnFirstFailure;
use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[StopOnFirstFailure]
class CommentRequest extends FormRequest
{
    public function resolveItem(): Blog | Recipe
    {
        $builder = match ($this->string('module')->toString()) {
            'blog' => Blog::query(),
            'recipe' => Recipe::query(),
            default => throw new NotFoundHttpException(),
        };

        return $builder->findOrFail($this->integer('id'));
    }

    public function rules(): array
    {
        return [
            'module' => ['required', Rule::in(['blog', 'recipe'])],
            'id' => ['required', 'numeric', 'bail', function (string $attribute, mixed $value, Closure $fail): void {
                $builder = match ((string) $this->string('module')) {
                    'blog' => Blog::query(),
                    'recipe' => Recipe::query(),
                    default => throw new NotFoundHttpException(),
                };

                $check = $builder->where('id', $value)->exists();

                if ( ! $check) {
                    $fail("The {$this->string('module')} can not be found.");
                }
            }],
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'comment' => ['required'],
        ];
    }

    public function comment(): array
    {
        return $this->only(['name', 'email', 'comment']);
    }
}
