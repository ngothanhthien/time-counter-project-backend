<?php

namespace App\Http\Middleware;

use App\Domain\Repositories\ProjectRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserOwnProjectMiddleware
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $projectId = $request->input('project_id');
        $userId = $request->user()->id;

        if (!$userId) {
            return response()->json([
                'message' => 'You are not allowed to access this project. You are not logged in.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$projectId) {
            return response()->json([
                'message' => 'Project ID is required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $ownedProject = $this->projectRepository->isBelongsToUser($projectId, $userId);

        if (!$ownedProject) {
            return response()->json([
                'message' => 'You are not allowed to access this project. This project does not belong to you.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
