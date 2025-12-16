<?php

namespace App\Application\Usecases\System;

use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use App\Domain\Entities\Project\ProjectTime;

class CheckpointTimer
{
    public function __construct(
        private readonly ProjectTimeRepositoryInterface $projectTimeRepository
    ) {}

    public function execute(int $timerId)
    {
        /** @var ProjectTime $timeEntry */
        $timeEntry = $this->projectTimeRepository->findById($timerId);
        $totalSeconds = now()->diffInSeconds($timeEntry->counted_at, true);

        $this->projectTimeRepository->update($timerId, [
            'counted_at' => now(),
            'seconds_counted' => $timeEntry->seconds_counted + $totalSeconds,
        ]);
    }
}
