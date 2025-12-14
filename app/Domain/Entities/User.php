<?php

namespace App\Domain\Entities;

use Spatie\LaravelData\Data;

class User extends Data
{
    public function __construct(
        public int $id,
        public string $access_token,
        public string $ip_address,

        public string $last_activity,
    ) {}
}
