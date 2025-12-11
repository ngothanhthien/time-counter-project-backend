<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\ProjectNoteRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\DataConverter;

class ProjectNoteController extends Controller
{
    public function __construct(
        private readonly ProjectNoteRepositoryInterface $projectNoteRepository,
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'note' => 'required|string',
        ]);

        $note = $this->projectNoteRepository->create([
            'title' => $request->input('title'),
            'note' => $request->input('note'),
            'status' => 'pending',
            'project_id' => $request->input('project_id'),
        ]);

        return response()->json($note, Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'status' => 'nullable|string|in:pending,completed',
        ]);

        $note = $this->projectNoteRepository->update($id, DataConverter::filterNulls([
            'title' => $request->input('title'),
            'note' => $request->input('note'),
            'status' => $request->input('status'),
        ]));

        return response()->json($note);
    }

    public function destroy(int $id)
    {
        $this->projectNoteRepository->delete($id);

        return response()->json(true);
    }
}
