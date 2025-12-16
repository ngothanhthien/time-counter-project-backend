<?php

namespace App\Http\Controllers;

use App\Application\Usecases\System\CheckpointTimer;
use App\Domain\Repositories\ProjectRepositoryInterface;
use App\Application\Usecases\User\CreateProject;
use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\DataConverter;
use App\Domain\Entities\Project\ProjectTime;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectTimeRepositoryInterface $projectTimeRepository
    ) {}

    public function index(Request $request)
    {
        $projects = $this->projectRepository->allBy(
            ['user_id' => $request->user()->id],
            ['notes', 'time_entries']
        );

        return response()->json($projects);
    }

    public function show(int $id)
    {
        /** @var ProjectTime[] $timeEntries */
        $timeEntries = $this->projectTimeRepository->allBy([
            'project_id' => $id,
            'is_counting' => true,
        ]);

        $action = app(CheckpointTimer::class);

        foreach ($timeEntries as $timeEntry) {
            $action->execute($timeEntry->id);
        }

        $project = $this->projectRepository->findById($id, [
            'notes' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'time_entries' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return response()->json($project);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $action = app(CreateProject::class);
        $project = $action->execute(
            $request->input('name'),
            $request->user()->id
        );

        return response()->json($project, Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:idle,processing,completed',
        ]);

        $project = $this->projectRepository->update(
            $id,
            DataConverter::filterNulls([
                'name' => $request->input('name'),
                'status' => $request->input('status'),
            ]),
        );

        return response()->json($project);
    }

    public function destroy(int $id)
    {
        $this->projectRepository->delete($id);

        return response()->json(true);
    }
}
