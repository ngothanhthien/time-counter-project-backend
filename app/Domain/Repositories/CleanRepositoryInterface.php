<?php

namespace App\Domain\Repositories;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

interface CleanRepositoryInterface
{
    public function findById(string|int $id, array $with = []): ?Data;
    public function findOrFail(string|int $id, array $with = []): Data;
    public function findBy(array $conditions, array $with = []): ?Data;
    public function all(array $with = []): DataCollection|array;
    public function allBy(array $conditions, array $with = [], array $select = ['*']): DataCollection|array;
    public function pluck(string $column, ?string $key = null, array $conditions = []): array;

    public function create(Data|array $data): Data;
    public function update(string|int $id, Data|array $data): Data;

    public function forceUpdate(string|int $id, Data|array $data): Data;
    public function createOrUpdate(Data|array $data, array $conditions = []): Data;

    public function updateBy(array $conditions, Data|array $data): int;
    public function forceUpdateBy(array $conditions, Data|array $data): int;
    public function delete(string|int $id): bool;
    public function deleteBy(array $conditions): bool;

    public function getPaginatedList(
        array $filters = [],
        int $perPage = 25,
        array $with = [],
        string $orderBy = 'created_at',
        string $orderDirection = 'desc'
    ): PaginatedDataCollection;
}
