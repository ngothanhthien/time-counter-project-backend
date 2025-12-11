<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectTimeController extends Controller
{
    public function __construct(
        private readonly ProjectTimeRepositoryInterface $projectTimeRepository,
    ) {}

    public function store(Request $request)
    {
        $time = $this->projectTimeRepository->create([
            'project_id' => $request->input('project_id'),
            'is_counting' => 0,
        ]);

        return response()->json($time, Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'is_counting' => 'required|integer',
        ]);

        $time = $this->projectTimeRepository->update($id, $request->validated());

        return response()->json($time);
    }

    public function destroy(int $id)
    {
        $this->projectTimeRepository->delete($id);

        return response()->json(true);
    }
}
