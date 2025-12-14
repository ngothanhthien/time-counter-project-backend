<?php

namespace App\Infrastructure\Repositories\Laravel;

use App\Domain\Entities\Project\Project as ProjectEntity;
use App\Domain\Repositories\ProjectRepositoryInterface;
use App\Models\Project as ProjectModel;
use Illuminate\Support\Facades\DB;

class ProjectRepository extends CleanRepositoriesAbstract implements ProjectRepositoryInterface
{
    protected string $dataClass = ProjectEntity::class;

    public function __construct(ProjectModel $model)
    {
        parent::__construct($model);
    }

    public function isBelongsToUser(int $projectId, int $userId): bool
    {
        return $this->model->where('id', $projectId)->where('user_id', $userId)->exists();
    }

    public function delete(string|int $projectId): bool
    {
        $project = $this->model->where('id', $projectId)->first();
        if (!$project) {
            return false;
        }

        DB::beginTransaction();
        try {
            $project->notes()->delete();
            $project->time_entries()->delete();
            $project->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
