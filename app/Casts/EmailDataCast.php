<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Comments\Comment;
use App\Models\Comments\CommentReply;
use App\Models\EatingOut\EateryReview;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<array{date?: Carbon, comment?: Comment, reply?: CommentReply, order?: ShopOrder, notifiable?: ShopCustomer}, string> */
class EmailDataCast implements CastsAttributes
{
    /**
     * @phpstan-param  string  $value
     *
     * @return array{date?: Carbon|null, comment?: Comment|null, reply?: CommentReply, order?: ShopOrder, notifiable?: ShopCustomer}
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        /** @var array{date?: string, comment?: array{id?: number, created_at?: string, name?: string}, reply?: array{id: string}, rating?: array{id: string}, order?: array{id: string}, notifiable?: array{id: string}} $data */
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
            $return['order'] = ShopOrder::query()->findOrFail($data['order']['id']);
        }

        if (isset($data['notifiable'])) {
            $return['notifiable'] = ShopCustomer::query()->findOrFail($data['notifiable']['id']);
        }

        return $return;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_encode($value);
    }
}
