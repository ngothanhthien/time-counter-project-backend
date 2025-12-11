<?php

namespace App\Application\Usecases\User;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Entities\User;
use Illuminate\Support\Str;

class RegisterUser
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new user or return an existing one
     * 1. Check if the user exists by IP address
     * 2. If the user exists, return the user
     * 3. If the user does not exist, create a new user
     * 4. Return the user
     *
     * @param mixed $ip
     * @return User
     */
    public function execute($ip): User
    {
        $user = $this->userRepository->findBy(['ip_address' => $ip]);

        if ($user) {
            return $user;
        }

        return $this->userRepository->create([
            'access_token' => Str::random(32),
            'ip_address' => $ip,
            'last_activity' => now(),
        ]);
    }
}
