<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Average;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Count;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Order;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Configuration implements Castable, Jsonable
{
    /** @var array<int, Where|Where[]> */
    protected array $wheres = [];

    /** @var Join[] */
    protected array $joins = [];

    /** @var Count[] */
    protected array $counts = [];

    /** @var Average[] */
    protected array $averages = [];

    /** @var Order[] */
    protected array $orderBy = [];

    protected ?int $limit = null;

    protected ?array $novaDisplay = null;

    protected mixed $novaLimit = null;

    public function __construct(
        array $wheres = [],
        array $joins = [],
        array $counts = [],
        array $averages = [],
        array $orderBy = [],
        ?int $limit = null,
        ?array $novaDisplay = null,
        mixed $novaLimit = null,
    ) {
        $this->wheres = $this->processWheres($wheres);
        $this->joins = array_map(fn ($join) => $join instanceof Join ? $join : new Join(...$join), $joins);
        $this->counts = array_map(fn ($count) => $count instanceof Count ? $count : new Count(...$count), $counts);
        $this->averages = array_map(fn ($average) => $average instanceof Average ? $average : new Average(...$average), $averages);
        $this->orderBy = array_map(fn ($order) => $order instanceof Order ? $order : new Order(...$order), $orderBy);
        $this->limit = $limit;
        $this->novaDisplay = $novaDisplay;
        $this->novaLimit = $novaLimit;
    }

    protected function processWheres(array $wheres): array
    {
        return array_map(function ($where) {
            if ($where instanceof Where) {
                return $where;
            }

            if (is_array($where) && isset($where[0]) && (is_array($where[0]) || $where[0] instanceof Where)) {
                return collect($this->processWheres($where));
            }

            return new Where(...$where);
        }, $wheres);
    }

    /** @return Collection<int, Where|Collection<int, Where>> */
    public function getWheres(): Collection
    {
        /** @phpstan-ignore argument.type */
        return $this->prepareWheres(collect($this->wheres));
    }

    /** @return Collection<int, Join> */
    public function getJoins(): Collection
    {
        return collect($this->joins);
    }

    /** @return Collection<int, Count> */
    public function getCounts(): Collection
    {
        return collect($this->counts);
    }

    /** @return Collection<int, Average> */
    public function getAverages(): Collection
    {
        return collect($this->averages);
    }

    /** @return Collection<int, Order> */
    public function getOrderings(): Collection
    {
        return collect($this->orderBy);
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /** @param Where|Where[] $where */
    public function addWhere(Where|array $where): self
    {
        if (is_array($where)) {
            $where = collect($where);
        }

        /** @phpstan-ignore-next-line  */
        $this->wheres[] = $where;

        return $this;
    }

    public function addJoin(Join $join): self
    {
        $this->joins[] = $join;

        return $this;
    }

    /**
     * @param Collection<int, Where|Collection<int, Where>> $wheres
     * @return Collection<int, Where|Collection<int, Where>>
     */
    protected function prepareWheres(Collection $wheres): Collection
    {
        /** @phpstan-ignore return.type */
        return $wheres->map(function (Where|Collection $where) {
            if ($where instanceof Where) {
                return $where;
            }

            /** @phpstan-ignore argument.type */
            return $this->prepareWheres($where);
        });
    }

    public function toJson($options = 0): string
    {
        return (string) json_encode([
            'wheres' => $this->wheres,
            'joins' => $this->joins,
            'counts' => $this->counts,
            'averages' => $this->averages,
            'orderBy' => $this->orderBy,
            'limit' => $this->limit,
            '_display' => $this->novaDisplay,
            '_limit' => $this->novaLimit,
        ], $options);
    }

    /** @return CastsAttributes<Configuration, Configuration> */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class () implements CastsAttributes {
            public function get(Model $model, string $key, mixed $value, array $attributes): Configuration
            {
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }

                $limit = data_get($value, 'limit');

                return new Configuration(
                    data_get($value, 'wheres', []),
                    data_get($value, 'joins', []),
                    data_get($value, 'counts', []),
                    data_get($value, 'averages', []),
                    data_get($value, 'orderBy', []),
                    $limit ? (int) $limit : null,
                    data_get($value, '_display'),
                    data_get($value, '_limit'),
                );
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): array
            {
                if ( ! $value instanceof Configuration) {
                    $value = $this->get($model, $key, $value, $attributes);
                }

                return [$key => $value->toJson()];
            }
        };
    }
}
