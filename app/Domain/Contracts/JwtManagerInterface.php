<?php

namespace App\Domain\Contracts;

use App\Domain\Data\TokenPayload;

interface JwtManagerInterface
{
    public function generateToken(TokenPayload $payload): string;
    public function parseToken(string $token): ?TokenPayload;
}
