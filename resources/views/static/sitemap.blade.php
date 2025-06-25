<urlset xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>always</changefreq>
        <priority>1.00</priority>
    </url>
    <url>
        <loc>{{ route('about') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.25</priority>
    </url>
    <url>
        <loc>{{ route('work-with-us') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ route('eating-out.app') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>

    <!-- Shop -->
    <url>
        <loc>{{ route('shop.index') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.95</priority>
    </url>
    @foreach($categories as $category)
        <url>
            <loc>{{ $category->absolute_link }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach($products as $product)
        <url>
            <loc>{{ $category->absolute_link }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach


    <!-- Blogs -->
    <url>
        <loc>{{ route('blog.index') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach($blogs as $blog)
        <url>
            <loc>{{ $blog->absolute_link }}</loc>
            <lastmod>{{ $blog->updated_at?->toW3cString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.75</priority>
        </url>
    @endforeach

    <!-- Recipes -->
    <url>
        <loc>{{ route('recipe.index') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach($recipes as $recipe)
        <url>
            <loc>{{ $recipe->absolute_link }}</loc>
            <lastmod>{{ $recipe->updated_at?->toW3cString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.75</priority>
        </url>
    @endforeach

    <!-- Where To Eat -->
    <url>
        <loc>{{ route('eating-out.landing') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('eating-out.browse') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('eating-out.index') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('eating-out.recommend.index') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.5</priority>
    </url>
    <!-- Counties -->
    @foreach($counties as $county)
        <url>
            <loc>{{ $county->absoluteLink() }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    <!-- Towns -->
    @foreach($towns as $town)
        <url>
            <loc>{{ $town->absoluteLink() }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.75</priority>
        </url>
    @endforeach

    <!-- Areas -->
    @foreach($areas as $area)
        <url>
            <loc>{{ $area->absoluteLink() }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    <!-- Eateries -->
    @foreach($eateries as $eatery)
        <url>
            <loc>{{ $eatery->absoluteLink() }}</loc>
            <lastmod>{{ $eatery->updated_at?->toW3cString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach


    <url>
        <loc>{{ route('eating-out.nationwide') }}</loc>
        <lastmod>{{ $recipe->updated_at?->toW3cString() }}</lastmod>
        <changefreq>always</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach($nationwideChains as $eatery)
        <url>
            <loc>{{ $eatery->absoluteLink() }}</loc>
            <lastmod>{{ $eatery->updated_at?->toW3cString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
    @foreach($nationwideBranches as $branch)
        <url>
            <loc>{{ $branch->absoluteLink() }}</loc>
            <lastmod>{{ $branch->updated_at?->toW3cString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
</urlset>
