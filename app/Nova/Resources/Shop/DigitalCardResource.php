<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\DigitalCardColor;
use App\Enums\Shop\DigitalCardIcon;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardType;
use App\Nova\Actions\Shop\PushDigitalCardToAppleWalletPasses;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class DigitalCardResource extends Resource
{
    public static $model = ShopDigitalCard::class;

    public static $title = 'title';

    public static $search = [
        'id', 'title', 'language', 'native_language',
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

                Slug::make('Slug')
                    ->from('Title')
                    ->onlyOnForms()
                    ->required()
                    ->fullWidth()
                    ->immutable(),

                BelongsTo::make('Type', 'type', DigitalCardTypeResource::class)
                    ->required()
                    ->fullWidth()
                    ->relatableQueryUsing(fn (NovaRequest $request, Builder $query) => $query->withoutGlobalScopes()),

                Text::make('Language')
                    ->required()
                    ->fullWidth()
                    ->placeholder('Spanish')
                    ->maxlength(50),

                Text::make('Native Language')
                    ->required()
                    ->onlyOnForms()
                    ->fullWidth()
                    ->placeholder('Español')
                    ->maxlength(50),

                Boolean::make('Live')
                    ->fullWidth(),
            ]),

            BelongsTo::make('Physical Product', 'product', Products::class)->onlyOnIndex(),

            Stack::make('Price', [
                Boolean::make('Use Shared Digital Card Price', fn (ShopDigitalCard $model) => $model->price()->doesntExist())->textAlign('left'),
                Line::make('Price', 'price.price')->displayUsing(fn (?string $price) => $price ? Str::of($price)->pipe(fn (Stringable $price) => number_format($price->toInteger() / 100, 2))->prepend('Individual Price £') : null),
                Line::make('Physical Bundle Price', 'price.price_with_physical_product')->displayUsing(fn (?string $price) => $price ? Str::of($price)->pipe(fn (Stringable $price) => number_format($price->toInteger() / 100, 2))->prepend('Physical Bundle Price £') : ''),
            ]),

            Panel::make('Linked Physical Product', [
                Boolean::make('Has Linked Product', 'enable_physical_addons')
                    ->onlyOnForms()
                    ->fullWidth(),

                BelongsTo::make('Physical Product', 'product', Products::class)
                    ->onlyOnForms()
                    ->searchable()
                    ->nullable()
                    ->fullWidth()
                    ->dependsOn(['type', 'enable_physical_addons'], function (BelongsTo $field, NovaRequest $request, FormData $data): void {
                        if ( ! $data->boolean('enable_physical_addons')) {
                            $field->hide();

                            return;
                        }

                        $field->show();

                        if ( ! $data->filled('type')) {
                            $field->readonly();

                            return;
                        }

                        $type = ShopDigitalCardType::query()->withoutGlobalScopes()->findOrFail($data->integer('type'));

                        $field
                            ->readonly(false)
                            ->relatableQueryUsing(fn (NovaRequest $request, Builder $query) => $query
                                ->whereRelation('categories', fn (Builder $relation) => $relation->where('category_id', $type->category_id))
                                ->whereHas('variants', fn ($query) => $query->where('live', true)), );
                    }),
            ]),

            Panel::make('Pricing', [
                Boolean::make('Use Shared Digital Card Price', 'shared_price')
                    ->resolveUsing(fn ($request, ShopDigitalCard $card) => $card->price()->doesntExist())
                    ->onlyOnForms()
                    ->default(true)
                    ->fullWidth(),

                Currency::make('Price', 'price.price')
                    ->onlyOnForms()
                    ->dependsOn(['shared_price'], function (Currency $field, NovaRequest $request, FormData $data): void {
                        if ($data->boolean('shared_price')) {
                            $field->hide();

                            return;
                        }

                        $field->show();
                    })
                    ->deferrable()
                    ->asMinorUnits()
                    ->required(),

                Currency::make('Extra price when bought with physical product', 'price.price_with_physical_product')
                    ->deferrable()
                    ->onlyOnForms()
                    ->asMinorUnits()
                    ->dependsOn(['enable_physical_addons', 'shared_price'], function (Currency $field, NovaRequest $request, FormData $data): void {
                        if ($data->boolean('shared_price')) {
                            $field->hide();

                            return;
                        }

                        $field->show();

                        if ( ! $data->boolean('enable_physical_addons')) {
                            $field->hide();

                            return;
                        }

                        $field->show();
                    })
                    ->required(),
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

    public function actions(NovaRequest $request): array
    {
        return [
            PushDigitalCardToAppleWalletPasses::make()
                ->confirmText('This will push the current card content to every Apple Wallet pass already issued for it, and notify the customers holding them. Are you sure?')
                ->confirmButtonText('Push to passes'),
        ];
    }

    public static function indexQuery(NovaRequest $request, \Illuminate\Contracts\Database\Eloquent\Builder $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->with(['price']);
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $card = $fillFields[0];

        unset($card->shared_price);

        return $fillFields;
    }
}
