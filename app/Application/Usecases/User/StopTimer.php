<?php

namespace App\Application\Usecases\User;

use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use App\Domain\Entities\Project\ProjectTime;
use Carbon\CarbonImmutable;

class StopTimer
{
    public function __construct(
        private readonly ProjectTimeRepositoryInterface $projectTimeRepository,
    ) {}

    public function execute(int $timerId)
    {
        /** @var ProjectTime $timer */
        $timer = $this->projectTimeRepository->findById($timerId);

        if (!$timer) {
            throw new \Exception('Timer not found');
        }

        if (!$timer->is_counting) {
            throw new \Exception('Timer is not counting');
        }

        $this->projectTimeRepository->update($timerId, [
            'is_counting' => 0,
            'counted_at' => null,
            'seconds_counted' => $timer->seconds_counted + $timer->counted_at->diffInSeconds(CarbonImmutable::now()),
        ]);
    }
}
