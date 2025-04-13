<?php

declare(strict_types=1);

namespace App\Models\Blogs;

use App\Concerns\CanBePublished;
use App\Concerns\ClearsCache;
use App\Concerns\Comments\Commentable;
use App\Concerns\DisplaysDates;
use App\Concerns\DisplaysMedia;
use App\Concerns\LinkableModel;
use App\Contracts\Comments\HasComments;
use App\Contracts\Search\IsSearchable;
use App\Jobs\OpenGraphImages\CreateBlogIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateHomePageOpenGraphImageJob;
use App\Legacy\HasLegacyImage;
use App\Legacy\Imageable;
use App\Models\Media;
use App\Scopes\LiveScope;
use App\Support\Collections\CanBeCollected;
use App\Support\Collections\Collectable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemaOrg\Blog as BlogSchema;
use Spatie\SchemaOrg\Schema;

/**
 * @implements Collectable<$this>
 * @implements HasComments<$this>
 */
class Blog extends Model implements Collectable, HasComments, HasMedia, IsSearchable
{
    /** @use CanBeCollected<$this> */
    use CanBeCollected;

    use CanBePublished;
    use ClearsCache;

    /** @use Commentable<$this> */
    use Commentable;

    use DisplaysDates;
    use DisplaysMedia;
    use HasLegacyImage;
    use Imageable;

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

    protected function linkRoot(): string
    {
        return 'blog';
    }

    public function schema(): BlogSchema
    {
        /** @var string $url */
        $url = config('app.url');

        return Schema::blog()
            ->author(Schema::person()->name('Alison Peters'))
            ->dateModified($this->updated_at)
            ->datePublished($this->created_at)
            ->description($this->meta_description)
            ->headline($this->title)
            ->image($this->main_image)
            ->mainEntityOfPage(Schema::webPage()->identifier($url))
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
