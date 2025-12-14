<?php

namespace App\Application\Usecases\User;

use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use App\Domain\Entities\Project\ProjectTime;
use Carbon\CarbonImmutable;

class StartTimer
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

        if ($timer->is_counting) {
            throw new \Exception('Timer is already counting');
        }

        $this->projectTimeRepository->update($timerId, [
            'is_counting' => 1,
            'counted_at' => CarbonImmutable::now(),
        ]);
    }
}
