<?php

namespace App\Domain\Repositories;

interface ProjectTimeRepositoryInterface extends CleanRepositoryInterface
{
    public function isBelongsToProject(int $timerId, int $projectId): bool;
}
