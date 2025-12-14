<?php

namespace App\Infrastructure\Repositories\Laravel;

use App\Domain\Entities\Project\ProjectTime as ProjectTimeEntity;
use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use App\Models\ProjectTime as ProjectTimeModel;

class ProjectTimeRepository extends CleanRepositoriesAbstract implements ProjectTimeRepositoryInterface
{
    protected string $dataClass = ProjectTimeEntity::class;

    public function __construct(ProjectTimeModel $model)
    {
        parent::__construct($model);
    }

    public function isBelongsToProject(int $timerId, int $projectId): bool
    {
        return $this->model->where('id', $timerId)->where('project_id', $projectId)->exists();
    }
}
