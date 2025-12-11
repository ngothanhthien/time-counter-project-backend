<?php

namespace App\Domain\Data;

use Spatie\LaravelData\Data;

class TokenPayload extends Data
{
    public function __construct(
        public int $id,
        public string $ip_address,
    ) {}
}
