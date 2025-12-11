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

    public function __construct(Model $model)
    {
        $this->model = $model;
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
        $record = $this->query()->with($with)->find($id);

        return $this->toEntity($record);
    }

    public function findOrFail(string|int $id, array $with = []): Data
    {
        $record = $this->query()->with($with)->findOrFail($id);

        return $this->toEntity($record);
    }

    public function findBy(array $conditions, array $with = []): ?Data
    {
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

        $record = $this->model->create($payload);

        return $this->toEntity($record);
    }

    public function update(string|int $id, Data|array $data): Data
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        $record = $this->query()->findOrFail($id);
        $record->update($payload);

        return $this->toEntity($record->refresh());
    }

    public function createOrUpdate(Data|array $data, array $conditions = []): Data
    {
        $payload = $data instanceof Data ? $data->toArray() : $data;

        $record = $this->model->updateOrCreate($conditions, $payload);

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
}
