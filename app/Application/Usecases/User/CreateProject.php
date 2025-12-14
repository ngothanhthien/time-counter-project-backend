<?php

namespace App\Application\Usecases\User;

use App\Domain\Repositories\ProjectRepositoryInterface;
use App\Domain\Repositories\ProjectNoteRepositoryInterface;
use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use App\Domain\Entities\Project\Project;

class CreateProject
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectNoteRepositoryInterface $projectNoteRepository,
        private readonly ProjectTimeRepositoryInterface $projectTimeRepository,
    ) {}

    public function execute(string $name, int $userId): Project
    {
        /** @var Project $project */
        $project = $this->projectRepository->create([
            'name' => $name,
            'user_id' => $userId,
        ]);

        $this->projectNoteRepository->create([
            'project_id' => $project->id,
            'title' => 'Note 1',
            'note' => 'Note 1 description',
        ]);

        $this->projectNoteRepository->create([
            'project_id' => $project->id,
            'title' => 'Note 2',
            'note' => 'Note 2 description',
        ]);

        $this->projectTimeRepository->create([
            'project_id' => $project->id,
            'user_id' => $userId,
        ]);

        return $project;
    }
}
