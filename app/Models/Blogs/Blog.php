<?php

declare(strict_types=1);

namespace App\Models\Blogs;

use App\Concerns\CanBePublished;
use App\Concerns\ClearsCache;
use App\Concerns\Comments\Commentable;
use App\Concerns\DisplaysDates;
use App\Concerns\DisplaysMedia;
use App\Concerns\Faqs\Faqable;
use App\Concerns\LinkableModel;
use App\Contracts\Comments\HasComments;
use App\Contracts\Faqs\HasFaqs;
use App\Contracts\Search\IsSearchable;
use App\Jobs\OpenGraphImages\CreateBlogIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateHomePageOpenGraphImageJob;
use App\Models\Media;
use App\Scopes\LiveScope;
use App\Support\Collections\CanBeCollected;
use App\Support\Collections\Collectable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemaOrg\BlogPosting as BlogPostingSchema;
use Spatie\SchemaOrg\Schema;

/**
 * @property int<1, max> $reading_time
 *
 * @implements Collectable<$this>
 * @implements HasComments<$this>
 * @implements HasFaqs<$this>
 */
class Blog extends Model implements Collectable, HasComments, HasFaqs, HasMedia, IsSearchable
{
    /** @use CanBeCollected<$this> */
    use CanBeCollected;

    use CanBePublished;
    use ClearsCache;

    /** @use Commentable<$this> */
    use Commentable;

    use DisplaysDates;

    use DisplaysMedia;
    /** @use Faqable<$this> */
    use Faqable;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    use LinkableModel;
    use Searchable;

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());

        static::saved(function (): void {
            if (config('coeliac.generate_og_images') === false) {
                return;
            }

            CreateBlogIndexPageOpenGraphImageJob::dispatch();
            CreateHomePageOpenGraphImageJob::dispatch();
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /** @param Builder<static> $query */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $query = $query->where('draft', false);

        if (app(Request::class)->wantsJson()) {
            return $query->where('id', $value);
        }

        return $query->where('slug', $value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('social')->singleFile();

        $this->addMediaCollection('primary')->singleFile();

        $this->addMediaCollection('body');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        if ( ! $media || $media->extension === 'webp') {
            return;
        }

        $this
            ->addMediaConversion('webp')
            ->performOnCollections('primary')
            ->nonQueued()
            ->format('webp');
    }

    /** @return Attribute<int<1, max>, never> */
    public function readingTime(): Attribute
    {
        return Attribute::get(fn (): int => max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 200)));
    }

    /** @return BelongsToMany<BlogTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogTag::class,
            'blog_assigned_tags',
            'blog_id',
            'tag_id'
        )->withTimestamps();
    }

    /** @return BelongsTo<BlogTag, $this> */
    public function primaryTag(): BelongsTo
    {
        return $this->belongsTo(BlogTag::class, 'primary_tag_id');
    }

    /** @return HasMany<BlogMetric, $this> */
    public function metrics(): HasMany
    {
        return $this->hasMany(BlogMetric::class);
    }

    protected function linkRoot(): string
    {
        return 'blog';
    }

    public function schema(): BlogPostingSchema
    {
        /** @var string $url */
        $url = config('app.url');

        return Schema::blogPosting()
            ->author(Schema::person()->name('Alison Peters'))
            ->dateModified($this->updated_at)
            ->datePublished($this->created_at)
            ->description($this->meta_description)
            ->headline($this->title)
            ->image($this->main_image)
            ->mainEntityOfPage(Schema::webPage()->identifier($this->absolute_link))
            ->publisher(
                Schema::organization()
                    ->name('Coeliac Sanctuary')
                    ->logo(Schema::imageObject()->url($url . '/images/logo.svg'))
            );
    }

    public function getScoutKey(): mixed
    {
        return $this->id;
    }

    public function toSearchableArray(): array
    {
        return $this->transform([
            'title' => $this->title,
            'description' => $this->description,
            'metas' => $this->meta_tags,
            'tags' => $this->tags->pluck('tag'),
            'updated_at' => $this->updated_at,
        ]);
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->live;
    }

    protected function cacheKey(): string
    {
        return 'blogs';
    }
}
