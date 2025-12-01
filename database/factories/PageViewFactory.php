<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Journeys\Journey;
use App\Models\Journeys\Page;
use App\Models\Journeys\PageView;

class PageViewFactory extends Factory
{
    protected $model = PageView::class;

    public function definition()
    {
        return [
            'journey_id' => self::factoryForModel(Journey::class),
            'page_id' => self::factoryForModel(Page::class),
        ];
    }
}
