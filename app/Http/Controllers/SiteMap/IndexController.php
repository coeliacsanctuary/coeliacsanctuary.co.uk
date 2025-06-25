<?php

declare(strict_types=1);

namespace App\Http\Controllers\SiteMap;

use App\Support\SiteMap\SiteMapGenerator;
use Illuminate\Http\Response;

class IndexController
{
    public function __invoke(SiteMapGenerator $siteMapGenerator): Response
    {
        return new Response(
            view('static.sitemap', [
                'blogs' => $siteMapGenerator->blogs(),
                'recipes' => $siteMapGenerator->recipes(),
                'counties' => $siteMapGenerator->counties(),
                'towns' => $siteMapGenerator->towns(),
                'areas' => $siteMapGenerator->areas(),
                'eateries' => $siteMapGenerator->eateries(),
                'nationwideChains' => $siteMapGenerator->nationwideChains(),
                'nationwideBranches' => $siteMapGenerator->nationwideBranches(),
                'categories' => $siteMapGenerator->categories(),
                'products' => $siteMapGenerator->products(),
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/xml'],
        );

    }
}
