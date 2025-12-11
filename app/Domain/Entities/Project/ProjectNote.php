<?php

namespace App\Domain\Entities\Project;

use Spatie\LaravelData\Data;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Date;

class ProjectNote extends Data
{
    public function __construct(
        public int $id,
        public int $project_id,
        public string $title,
        public string $note,
        public string $status,

        #[Date]
        public CarbonImmutable $created_at,

        #[Date]
        public CarbonImmutable $updated_at,
    ) {}
}
