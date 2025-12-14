<?php

namespace App\Domain\Repositories;

interface ProjectNoteRepositoryInterface extends CleanRepositoryInterface
{
    public function isBelongsToProject(int $noteId, int $projectId): bool;
}
