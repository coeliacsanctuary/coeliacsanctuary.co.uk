<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Redirect;
use Illuminate\Database\Seeder;

class RedirectsSeeder extends Seeder
{
    public function run(): void
    {
        $legacyForgeRedirects = [
            '/shop/cs-shop-adm/' => '/cs-adm',
            '/shop/cs-shop-adm/*' => '/cs-adm',
            '/blog/gluten-free-chocolate-and-sweets$' => '/blog/gluten-free-chocolate-and-sweets-uk',
            '/blog/uk-gluten-free-chocolate-and-sweets-2018-list' => '/blog/gluten-free-chocolate-and-sweets-uk',
            '/recipe/gluten-free-bacon-and-honey-crepes' => '/recipe/gluten-free-bacon-and-maple-crepes',
            '/recipe/gluten-free-brie-bacon-potato-skins' => '/recipe/gluten-free-three-cheese-and-bacon-loaded-skins',
            '/recipe/gluten-free-death-by-coconut$' => '/recipe/gluten-free-death-by-coconut-cake',
            '/blog/gluten-free-easter-eggs-2016' => '/blog/gluten-free-easter-eggs-uk',
            '/blog/gluten-free-easter-eggs-2017' => '/blog/gluten-free-easter-eggs-uk',
            '/blog/gluten-free-easter-eggs-2018' => '/blog/gluten-free-easter-eggs-uk',
            '/blog/gluten-free-easter-eggs-2019' => '/blog/gluten-free-easter-eggs-uk',
            '/recipe/gluten-free-plain-scones' => '/recipe/gluten-free-plain-drop-scones',
            '/shop/coeliac-and-other-dietary-needs-travel-cards' => '/shop',
            '/shop/coeliac-and-other-dietary-needs-travel-cards/*' => '/shop',
            '/recipe/gluten-free-dairy-free-bread-butter-pudding' => '/recipe/gluten-free-individual-bread-and-butter-puddings',
            '/shop/product/gluten-free-small-stickers-limited-availability' => '/shop/product/gluten-free-small-stickers',
            '/shop/coeliac-cards$' => '/shop/coeliac-plus-other-allergen-cards',
        ];

        $deprecatedPages = [
            '/info' => '/',
            '/info/coeliac' => '/',
            '/info/shopping-list' => '/',
            '/info/storecupboard-check' => '/',
            '/info/gluten-challenge' => '/',
            '/faq' => '/',
            '/member/dashboard/*' => '/',
        ];

        foreach ([...$legacyForgeRedirects, ...$deprecatedPages] as $from => $to) {
            Redirect::query()->updateOrCreate([
                'from' => $from,
                'to' => $to,
            ]);
        }
    }
}
