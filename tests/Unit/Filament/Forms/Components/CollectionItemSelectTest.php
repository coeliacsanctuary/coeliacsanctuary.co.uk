<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\CollectionItemSelect;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Forms\Components\Hidden;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class CollectionItemSelectTest extends TestCase
{
    use BuildsFilamentSchemas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itFindsABlogByTitle(): void
    {
        $wanted = $this->create(Blog::class, ['title' => 'How To Make Gluten Free Bread']);
        $this->create(Blog::class, ['title' => 'Where To Eat In Crewe']);

        $this->assertSame([$wanted->id], $this->searchIds(Blog::class, 'Gluten Free Bread'));
    }

    #[Test]
    public function itFindsABlogBySlug(): void
    {
        $wanted = $this->create(Blog::class, ['title' => 'A Loaf', 'slug' => 'gluten-free-bread']);
        $this->create(Blog::class, ['title' => 'Something Else', 'slug' => 'where-to-eat']);

        $this->assertSame([$wanted->id], $this->searchIds(Blog::class, 'gluten-free-bread'));
    }

    #[Test]
    public function itFindsARecipeByTitle(): void
    {
        $wanted = $this->create(Recipe::class, ['title' => 'Gluten Free Victoria Sponge']);
        $this->create(Recipe::class, ['title' => 'Gluten Free Flapjacks']);

        $this->assertSame([$wanted->id], $this->searchIds(Recipe::class, 'Victoria Sponge'));
    }

    #[Test]
    public function itFindsAnEateryByName(): void
    {
        $wanted = $this->create(Eatery::class, ['name' => 'The Gluten Free Kitchen']);
        $this->create(Eatery::class, ['name' => 'Somewhere Else']);

        $this->assertSame([$wanted->id], $this->searchIds(Eatery::class, 'Gluten Free Kitchen'));
    }

    #[Test]
    public function itFindsAnEateryByItsInfo(): void
    {
        $wanted = $this->create(Eatery::class, ['name' => 'The Kitchen', 'info' => 'Dedicated gluten free bakery']);
        $this->create(Eatery::class, ['name' => 'Somewhere Else', 'info' => 'Has a separate fryer']);

        $this->assertSame([$wanted->id], $this->searchIds(Eatery::class, 'Dedicated gluten free'));
    }

    #[Test]
    public function itFindsANationwideBranchByName(): void
    {
        $eatery = $this->create(Eatery::class);

        $wanted = $this->build(NationwideBranch::class)->forEatery($eatery)->create(['name' => 'Crewe']);
        $this->build(NationwideBranch::class)->forEatery($eatery)->create(['name' => 'Nantwich']);

        $this->assertSame([$wanted->id], $this->searchIds(NationwideBranch::class, 'Crewe'));
    }

    #[Test]
    public function itFindsRecordsThatArentLive(): void
    {
        $blog = $this->build(Blog::class)->notLive()->create(['title' => 'Gluten Free Bread']);

        $this->assertSame([$blog->id], $this->searchIds(Blog::class, 'Gluten Free Bread'));
    }

    #[Test]
    public function itFindsNothingWithoutAnItemType(): void
    {
        $this->create(Blog::class, ['title' => 'Gluten Free Bread']);

        $this->assertSame([], $this->field(null)->getSearchResults('Gluten Free Bread'));
    }

    #[Test]
    public function itReturnsAtMostTenResults(): void
    {
        $this->create(Blog::class, 12, ['title' => 'Gluten Free Bread']);

        $this->assertCount(10, $this->field(Blog::class)->getSearchResults('Gluten Free Bread'));
    }

    #[Test]
    public function itShowsTheBlogTitleInTheResult(): void
    {
        $blog = $this->create(Blog::class, ['title' => 'How To Make Gluten Free Bread']);

        $this->assertStringContainsString(
            'How To Make Gluten Free Bread',
            $this->field(Blog::class)->getSearchResults('Gluten Free Bread')[$blog->id]
        );
    }

    #[Test]
    public function itShowsTheEateryAddressInTheResult(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'The Kitchen', 'address' => '12 Market Street']);

        $this->assertStringContainsString(
            '12 Market Street',
            $this->field(Eatery::class)->getSearchResults('The Kitchen')[$eatery->id]
        );
    }

    #[Test]
    public function itShowsTheParentEateryInfoForANationwideBranch(): void
    {
        $eatery = $this->create(Eatery::class, ['info' => 'Dedicated gluten free menu']);
        $branch = $this->build(NationwideBranch::class)->forEatery($eatery)->create(['name' => 'Crewe']);

        $this->assertStringContainsString(
            'Dedicated gluten free menu',
            $this->field(NationwideBranch::class)->getSearchResults('Crewe')[$branch->id]
        );
    }

    #[Test]
    public function itEscapesTheTitleInTheResult(): void
    {
        $blog = $this->create(Blog::class, ['title' => 'Bread & <script>alert(1)</script>']);

        $result = $this->field(Blog::class)->getSearchResults('Bread');

        $this->assertStringNotContainsString('<script>', $result[$blog->id]);
        $this->assertStringContainsString('&lt;script&gt;', $result[$blog->id]);
    }

    #[Test]
    public function itLabelsTheSelectedItem(): void
    {
        $blog = $this->create(Blog::class, ['title' => 'How To Make Gluten Free Bread']);

        $this->assertStringContainsString(
            'How To Make Gluten Free Bread',
            (string) $this->field(Blog::class, $blog->id)->getOptionLabel()
        );
    }

    #[Test]
    public function itLabelsASelectedItemThatIsNoLongerLive(): void
    {
        $blog = $this->build(Blog::class)->notLive()->create(['title' => 'How To Make Gluten Free Bread']);

        $this->assertStringContainsString(
            'How To Make Gluten Free Bread',
            (string) $this->field(Blog::class, $blog->id)->getOptionLabel()
        );
    }

    #[Test]
    public function itHasNoLabelForAnItemThatNoLongerExists(): void
    {
        $this->assertNull($this->field(Blog::class, 999)->getOptionLabel(withDefault: false));
    }

    /** @return array<int, int> */
    protected function searchIds(string $type, string $search): array
    {
        return array_keys($this->field($type)->getSearchResults($search));
    }

    protected function field(?string $type, ?int $value = null): CollectionItemSelect
    {
        return $this->mountedComponent('item_id', [
            Hidden::make('item_type')->default($type),
            CollectionItemSelect::make('item_id')->default($value),
        ]);
    }
}
