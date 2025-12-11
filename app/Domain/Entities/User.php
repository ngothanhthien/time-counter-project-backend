<?php

namespace App\Domain\Entities;

use Spatie\LaravelData\Data;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Date;

class User extends Data
{
    public function __construct(
        public int $id,
        public string $access_token,
        public string $ip_address,

        #[Date]
        public CarbonImmutable $last_activity,
    ) {}
}
