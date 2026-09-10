<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\DigitalCardColor;
use App\Enums\Shop\DigitalCardIcon;
use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\DigitalCards;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class DigitalCardResource extends Resource
{
    public static $model = DigitalCards::class;

    public static $title = 'title';

    public static $search = [
        'id', 'type', 'title', 'language', 'native_language',
    ];

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Panel::make('Card Details', [
                Text::make('Title')
                    ->required()
                    ->fullWidth()
                    ->maxlength(100),

                Select::make('Type')
                    ->required()
                    ->fullWidth()
                    ->options(DigitalCardType::class),

                Text::make('Language')
                    ->required()
                    ->fullWidth()
                    ->placeholder('Spanish')
                    ->maxlength(50),

                Text::make('Native Language')
                    ->required()
                    ->fullWidth()
                    ->placeholder('Español')
                    ->maxlength(50),

                BelongsTo::make('Physical Product', 'product', Products::class)
                    ->searchable()
                    ->nullable()
                    ->fullWidth()
                    ->dependsOn(['type'], function (BelongsTo $field, NovaRequest $request, FormData $data): void {
                        if ( ! $data->filled('type')) {
                            $field->readonly();

                            return;
                        }

                        $type = DigitalCardType::from($data->string('type')->toString());

                        $field
                            ->readonly(false)
                            ->relatableQueryUsing(fn(NovaRequest $request, Builder $query) => $query
                                ->whereRelation('categories', fn (Builder $relation) => $relation->where('category_id', $type->categoryId()))
                                ->whereHas('variants', fn ($query) => $query->where('live', true)),);
                    }),
            ]),

            Panel::make('Card Intro', [
                Text::make('Title', 'card_title')
                    ->required()
                    ->fullWidth()
                    ->onlyOnForms()
                    ->placeholder('Gluten Free Travel Card')
                    ->maxLength(256),

                Textarea::make('Intro Paragraph', 'card_intro')
                    ->required()
                    ->fullWidth()
                    ->onlyOnForms()
                    ->rows(3)
                    ->placeholder('I have Coeliac Disease and follow a strict gluten free diet. I cannot eat any foods which contain gluten, small amounts can make me sick.'),
            ])->help('The top of the card, in the native language'),

            Panel::make('Can\'t Eat Section', [
                Select::make('Section Colour', 'cant_eat_colour')
                    ->required()
                    ->fullWidth()
                    ->options(DigitalCardColor::class)
                    ->default(DigitalCardColor::RED)
                    ->onlyOnForms(),

                Select::make('Icon', 'cant_eat_icon')
                    ->required()
                    ->fullWidth()
                    ->options(DigitalCardIcon::class)
                    ->default(DigitalCardIcon::CROSS)
                    ->onlyOnForms(),

                Text::make('Label', 'cant_eat_label')
                    ->required()
                    ->fullWidth()
                    ->onlyOnForms()
                    ->placeholder('I can not eat')
                    ->maxLength(50),

                Text::make('Items', 'cant_eat_items')
                    ->required()
                    ->fullWidth()
                    ->onlyOnForms()
                    ->placeholder('Wheat, durum, semolina, couscous, bulgur, rye, barley, oats'),
            ])->help('In the native language'),

            Panel::make('Can Eat Section', [
                Select::make('Section Colour', 'can_eat_colour')
                    ->required()
                    ->fullWidth()
                    ->options(DigitalCardColor::class)
                    ->default(DigitalCardColor::GREEN)
                    ->onlyOnForms(),

                Select::make('Icon', 'can_eat_icon')
                    ->required()
                    ->fullWidth()
                    ->options(DigitalCardIcon::class)
                    ->default(DigitalCardIcon::TICK)
                    ->onlyOnForms(),

                Text::make('Label', 'can_eat_label')
                    ->required()
                    ->fullWidth()
                    ->onlyOnForms()
                    ->placeholder('I can eat')
                    ->maxLength(50),

                Text::make('Items', 'can_eat_items')
                    ->required()
                    ->fullWidth()
                    ->onlyOnForms()
                    ->placeholder('Fruit and vegetables, meat and fish, rice, potatoes, corn, egg, cheese, milk, beans and nuts'),
            ])->help('In the native language'),

            Panel::make('Card Outro', [
                Textarea::make('Outro Paragraph', 'card_outro')
                    ->required()
                    ->fullWidth()
                    ->onlyOnForms()
                    ->rows(3)
                    ->placeholder('Cross contamination is also an issue, please prepare gluten free separate. Cooking my food in fryers also used for gluten containing products, sharing toasters and preparing food on contaminated surfaces can all make me very sick.'),

                Text::make('Thank you line', 'card_thank_you')
                    ->required()
                    ->fullWidth()
                    ->maxLength(256)
                    ->placeholder('Thank you for helping me eat safely.')
                    ->onlyOnForms(),
            ])->help('In the native language'),
        ];
    }
}
