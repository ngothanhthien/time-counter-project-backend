<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\ProjectRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\DataConverter;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository
    ) {}

    public function index(Request $request)
    {
        $projects = $this->projectRepository->allBy(['user_id' => $request->user()->id]);

        return response()->json($projects);
    }

    public function show(int $id)
    {
        $project = $this->projectRepository->findById($id, ['notes', 'time_entries']);

        return response()->json($project);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = $this->projectRepository->create([
            'name' => $request->input('name'),
            'status' => 'idle',
            'user_id' => $request->user()->id,
        ]);

        return response()->json($project, Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:idle,processing,completed',
        ]);

        $project = $this->projectRepository->update($id, DataConverter::filterNulls([
            'name' => $request->input('name'),
            'status' => $request->input('status'),
        ]));

        return response()->json($project);
    }

    public function destroy(int $id)
    {
        $this->projectRepository->delete($id);

        return response()->json(true);
    }
}
