<?php

namespace App\Http\Controllers;

use App\Application\Usecases\User\StartTimer;
use App\Application\Usecases\User\StopTimer;
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

    public function start(Request $request, int $id)
    {
        $action = app(StartTimer::class);
        $action->execute($id);

        return response()->json(true);
    }

    public function stop(int $id)
    {
        $action = app(StopTimer::class);
        $action->execute($id);

        return response()->json(true);
    }

    public function destroy(int $id)
    {
        $this->projectTimeRepository->delete($id);

        return response()->json(true);
    }

    public function updateTime(int $id, Request $request)
    {
        $request->validate([
            'seconds_counted' => 'required|integer',
        ]);

        $this->projectTimeRepository->update($id, [
            'seconds_counted' => $request->input('seconds_counted'),
        ]);

        return response()->json(true);
    }
}
