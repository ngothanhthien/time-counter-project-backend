<?php

namespace App\Http\Middleware;

use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimerBelongToProjectMiddleware
{
    public function __construct(
        private readonly ProjectTimeRepositoryInterface $projectTimeRepository,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $isBelongsToProject = $this->projectTimeRepository->isBelongsToProject(
            $request->route('id'),
            $request->input('project_id')
        );

        if (!$isBelongsToProject) {
            return response()->json([
                'message' => 'Timer does not belong to project',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
