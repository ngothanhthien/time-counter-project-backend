<?php

namespace App\Domain\Repositories;

interface ProjectRepositoryInterface extends CleanRepositoryInterface
{
    public function isBelongsToUser(int $projectId, int $userId): bool;
}
