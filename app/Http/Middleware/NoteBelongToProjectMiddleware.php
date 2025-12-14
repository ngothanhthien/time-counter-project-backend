<?php

namespace App\Http\Middleware;

use App\Domain\Repositories\ProjectNoteRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoteBelongToProjectMiddleware
{
    public function __construct(
        private readonly ProjectNoteRepositoryInterface $projectNoteRepository,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $isBelongsToProject = $this->projectNoteRepository->isBelongsToProject(
            $request->route('id'),
            $request->input('project_id')
        );

        if (!$isBelongsToProject) {
            return response()->json([
                'message' => 'Note does not belong to project',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
