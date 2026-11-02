<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Arr::macro('getAsInt', function (array $array, string $key, int $default = 0): int {
            /** @var int | null $get */
            $get = Arr::get($array, $key, $default);

            if ( ! $get) {
                $get = $default;
            }

            return $get;
        });

        Arr::macro('getAsFloat', function (array $array, string $key, int $default = 0): float {
            /** @var float | null $get */
            $get = Arr::get($array, $key, $default);

            if ( ! $get) {
                $get = $default;
            }

            return $get;
        });

        Arr::macro('getAsString', function (array $array, string $key, string $default = ''): string {
            /** @var string | null $get */
            $get = Arr::get($array, $key, $default);

            if ( ! $get) {
                $get = $default;
            }

            return $get;
        });

        Arr::macro('getAsNullableString', function (array $array, string $key, ?string $default = null): ?string {
            /** @var string | null $get */
            $get = Arr::get($array, $key, $default);

            return $get;
        });

        Arr::macro('average', function (?array $values): ?float {
            if ( ! $values) {
                return null;
            }

            return round(array_sum($values) / count($values) * 2) / 2;
        });

        /** @return Collection<int, string> */
        Str::macro('explode', function (string $str, string $separator = ' '): Collection {
            /**
             * @var non-empty-string $str
             * @var non-empty-string $separator
             */
            return collect(explode($separator, $str));
        });
    }
}
