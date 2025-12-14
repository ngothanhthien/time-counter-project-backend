<?php

namespace App\Domain\Entities\Project;

use Spatie\LaravelData\Data;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Date;

class ProjectTime extends Data
{
    public function __construct(
        public int $id,
        public int $project_id,
        public int $user_id,
        public int $seconds_counted,
        public int $is_counting,

        #[Date]
        public ?CarbonImmutable $counted_at = null,

        #[Date]
        public CarbonImmutable $created_at,

        #[Date]
        public CarbonImmutable $updated_at,
    ) {}
}
