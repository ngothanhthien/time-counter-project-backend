<?php

namespace App\Infrastructure\Repositories\Laravel;

use App\Domain\Entities\Project\ProjectNote as ProjectNoteEntity;
use App\Domain\Repositories\ProjectNoteRepositoryInterface;
use App\Models\ProjectNote as ProjectNoteModel;

class ProjectNoteRepository extends CleanRepositoriesAbstract implements ProjectNoteRepositoryInterface
{
    protected string $dataClass = ProjectNoteEntity::class;

    public function __construct(ProjectNoteModel $model)
    {
        parent::__construct($model);
    }
}
