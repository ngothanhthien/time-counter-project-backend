<?php

namespace App\Infrastructure\Repositories\Laravel;

use App\Domain\Repositories\CleanRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;
use Closure;

abstract class CleanRepositoriesAbstract implements CleanRepositoryInterface
{
    protected Model $model;

    protected string $dataClass;
    protected array $defaultRelations = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function mergeWith(array $runtimeWith): array
    {
        return array_unique(array_merge($this->defaultRelations, $runtimeWith));
    }

    protected function toEntity(mixed $source): mixed
    {
        if ($source === null) return null;

        if ($source instanceof \Illuminate\Support\Collection || is_array($source)) {
            return $this->dataClass::collect($source, \Spatie\LaravelData\DataCollection::class);
        }

        if ($source instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
             return $this->dataClass::collect($source, \Spatie\LaravelData\PaginatedDataCollection::class);
        }

        return $this->dataClass::from($source);
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function findById(string|int $id, array $with = []): ?Data
    {
        $with = $this->mergeWith($with);
        $record = $this->query()->with($with)->find($id);

        return $this->toEntity($record);
    }

    public function findOrFail(string|int $id, array $with = []): Data
    {
        $with = $this->mergeWith($with);
        $record = $this->query()->with($with)->findOrFail($id);

        return $this->toEntity($record);
    }

    public function findBy(array $conditions, array $with = []): ?Data
    {
        $with = $this->mergeWith($with);
        $query = $this->query()->with($with);
        $this->applyConditions($conditions, $query);

        return $this->toEntity($query->first());
    }

    public function all(array $with = []): \Spatie\LaravelData\DataCollection|array
    {
        $records = $this->query()->with($with)->get();

        return $this->toEntity($records);
    }

    public function allBy(array $conditions, array $with = [], array $select = ['*']): \Spatie\LaravelData\DataCollection|array
    {
        $query = $this->query()->with($with)->select($select);

        $this->applyConditions($conditions, $query);

        return $this->toEntity($query->get());
    }

    public function pluck(string $column, ?string $key = null, array $conditions = []): array
    {
        $query = $this->query();
        $this->applyConditions($conditions, $query);

        return $query->pluck($column, $key)->all();
    }

    protected function applyConditions(array $where, Builder $query): void
    {
        foreach ($where as $field => $value) {
            // Case 1: Closure custom query
            if ($value instanceof Closure) {
                $value($query);
                continue;
            }

            // Case 2: Array query ['field', 'operator', 'value']
            if (is_array($value)) {
                if (count($value) === 3) {
                    [$field, $operator, $val] = $value;

                    switch (strtoupper($operator)) {
                        case 'IN':
                            $query->whereIn($field, $val);
                            break;
                        case 'NOT_IN':
                            $query->whereNotIn($field, $val);
                            break;
                        default:
                            $query->where($field, $operator, $val);
                    }
                }
                continue;
            }

            // Case 3: Simple Key-Value
            $query->where($field, $value);
        }
    }

    public function create(Data|array $data): Data
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        $record = $this->model->create($payload)->refresh();

        return $this->toEntity($record);
    }

    public function update(string|int $id, Data|array $data): Data
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        $cleanPayload = array_filter($payload, function ($value) {
            return $value !== null && $value !== '';
        });

        return $this->performUpdate($id, $cleanPayload);
    }

    public function forceUpdate(string|int $id, Data|array $data): Data
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        return $this->performUpdate($id, $payload);
    }

    protected function performUpdate(string|int $id, array $attributes): Data
    {
        $record = $this->query()->findOrFail($id);

        if (!empty($attributes)) {
            $record->update($attributes);
        }

        return $this->toEntity($record->refresh());
    }

    public function updateBy(array $conditions, Data|array $data): int
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        $cleanPayload = array_filter($payload, function ($value) {
            return $value !== null && $value !== '';
        });

        return $this->performBulkUpdate($conditions, $cleanPayload);
    }

    public function forceUpdateBy(array $conditions, Data|array $data): int
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        return $this->performBulkUpdate($conditions, $payload);
    }

    protected function performBulkUpdate(array $conditions, array $attributes): int
    {
        if (empty($attributes)) {
            return 0;
        }

        $query = $this->query();

        $this->applyConditions($conditions, $query);

        return $query->update($attributes);
    }

    public function createOrUpdate(Data|array $data, array $conditions = []): Data
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        $record = $this->model->updateOrCreate($conditions, $payload)->refresh();

        return $this->toEntity($record);
    }

    public function delete(string|int $id): bool
    {
        $record = $this->query()->findOrFail($id);
        return $record->delete();
    }

    public function deleteBy(array $conditions): bool
    {
        $query = $this->query();
        $this->applyConditions($conditions, $query);

        return $query->delete() > 0;
    }

    public function getPaginatedList(
        array $filters = [],
        int $perPage = 25,
        array $with = [],
        string $orderBy = 'created_at',
        string $orderDirection = 'desc'
    ): \Spatie\LaravelData\PaginatedDataCollection
    {
        $query = $this->query()->with($with);

        if (method_exists($this->model, 'scopeApplyFilter')) {
            $query->applyFilter($filters);
        }

        $query->orderBy($orderBy, $orderDirection);

        return $this->toEntity($query->paginate($perPage));
    }
}
