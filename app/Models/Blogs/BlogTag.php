<?php

declare(strict_types=1);

namespace App\Models\Blogs;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class BlogTag extends Model
{
    use HasSlug;

    /** @return BelongsToMany<Blog, $this> */
    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_assigned_tags', 'tag_id', 'blog_id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('tag')
            ->saveSlugsTo('slug');
    }

    public function resolveRouteBinding($value, $field = null): self
    {
        return $this->newQuery()->where('slug', $value)->firstOrFail();
    }

    /** @return Attribute<string, never> */
    public function novaTitle(): Attribute
    {
        return Attribute::get(fn() => "{$this->tag} - ({$this->blogs()->count()} blogs)");
    }

    public function link(): string
    {
        return route('blog.index.tags', $this->slug);
    }
}
