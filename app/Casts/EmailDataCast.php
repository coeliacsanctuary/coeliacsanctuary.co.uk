<?php

declare(strict_types=1);

namespace App\Casts;

use App\Actions\Shop\PrepareOrderForEmailAction;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReply;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/** @implements CastsAttributes<array{date?: Carbon, comment?: Comment, reply?: CommentReply, order?: ShopOrder, notifiable?: ShopCustomer}, string> */
class EmailDataCast implements CastsAttributes
{
    /**
     * @phpstan-param  string  $value
     *
     * @return array{date?: Carbon|null, comment?: Comment|null, reply?: CommentReply, rating?: EateryReview, order?: ShopOrder, notifiable?: ShopCustomer, recommendation?: EateryRecommendation, eatery?: Eatery, branch?: NationwideBranch, nearbyEateries?: Collection<int, Eatery>}
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        /** @var array{date?: string, comment?: array{id?: number, created_at?: string, name?: string}, reply?: array{id: string}, rating?: array{id: string}, order?: array{id: string}, notifiable?: array{id: string}, recommendation?: array{id: string}, eatery?: array{id: string}, branch?: array{id: string}, nearbyEateries?: array{array{id: string}}, ...<string, scalar>} $data */
        $data = json_decode($value, true);

        $return = [];

        if (isset($data['date'])) {
            $return['date'] = Carbon::make($data['date']);
        }

        if (isset($data['comment'])) {
            if (isset($data['comment']['id'])) {
                $return['comment'] = Comment::query()->findOrFail($data['comment']['id']);
            } elseif (isset($data['comment']['created_at'], $data['comment']['name'])) {
                $return['comment'] = Comment::query()
                    ->where('created_at', $data['comment']['created_at'])
                    ->where('name', $data['comment']['name'])
                    ->firstOrFail();
            } else {
                $return['comment'] = null;
            }
        }

        if (isset($data['reply'])) {
            $return['reply'] = CommentReply::query()->findOrFail($data['reply']['id']);
        }

        if (isset($data['rating'])) {
            $return['rating'] = EateryReview::query()->findOrFail($data['rating']['id']);
        }

        if (isset($data['order'])) {
            /** @var ShopOrder $order */
            $order = ShopOrder::query()->findOrFail($data['order']['id']);

            $return['order'] = app(PrepareOrderForEmailAction::class)->handle($order);
        }

        if (isset($data['notifiable'])) {
            $return['notifiable'] = ShopCustomer::query()->findOrFail($data['notifiable']['id']);
        }

        if (isset($data['recommendation'])) {
            $return['recommendation'] = EateryRecommendation::query()->findOrFail($data['recommendation']['id']);
        }

        if (isset($data['eatery'])) {
            $return['eatery'] = Eatery::query()->findOrFail($data['eatery']['id']);
        }

        if (isset($data['branch'])) {
            $return['branch'] = NationwideBranch::query()->findOrFail($data['branch']['id']);
        }

        if (isset($data['nearbyEateries'])) {
            $return['nearbyEateries'] = collect($data['nearbyEateries'])->map(fn ($eatery) => Eatery::query()->findOrFail($eatery['id']));
        }

        $modelKeys = ['date', 'comment', 'reply', 'rating', 'order', 'notifiable', 'recommendation', 'eatery', 'branch', 'nearbyEateries'];

        foreach ($data as $key => $value) {
            if ( ! in_array($key, $modelKeys) && is_scalar($value)) {
                $return[$key] = $value;
            }
        }

        /** @var array{date?: Carbon|null, comment?: Comment|null, reply?: CommentReply, rating?: EateryReview, order?: ShopOrder, notifiable?: ShopCustomer, recommendation?: EateryRecommendation, eatery?: Eatery, branch?: NationwideBranch, nearbyEateries?: Collection<int, Eatery>} $return */
        return $return;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_encode($this->toReferences(is_array($value) ? $value : []));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function toReferences(array $data): array
    {
        $references = [];

        foreach ($data as $key => $value) {
            if ($value instanceof Carbon) {
                $references[$key] = $value->toDateTimeString();

                continue;
            }

            if ($value instanceof Comment) {
                $references[$key] = [
                    'id' => $value->getKey(),
                    'created_at' => $value->created_at?->toDateTimeString(),
                    'name' => $value->name,
                ];

                continue;
            }

            if ($value instanceof Model) {
                $references[$key] = ['id' => $value->getKey()];

                continue;
            }

            if ($value instanceof Collection) {
                $references[$key] = $value
                    ->filter(fn (mixed $item) => $item instanceof Model)
                    ->map(fn (Model $item) => ['id' => $item->getKey()])
                    ->values()
                    ->all();

                continue;
            }

            if (is_scalar($value) || $value === null) {
                $references[$key] = $value;
            }
        }

        return $references;
    }
}
