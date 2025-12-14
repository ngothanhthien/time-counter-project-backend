<?php

namespace App\Domain\Entities\Project;

use Spatie\LaravelData\Data;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Date;

class Project extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public int $user_id,

        #[Date]
        public CarbonImmutable $created_at,

        #[Date]
        public CarbonImmutable $updated_at,

       /** @var ProjectTime[] */
        public array $time_entries = [],

       /** @var ProjectNote[] */
        public array $notes = [],
    ) {}
}
