<?php

namespace App\Http\Controllers;

use App\Application\Usecases\User\RegisterUser;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\JwtManagerInterface;
use Illuminate\Http\Request;
use App\Domain\Data\TokenPayload;
use App\Domain\Entities\User;

class AuthController extends Controller {
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly JwtManagerInterface $jwtManager
    ){}

    public function me(Request $request) {
        return response()->json($request->user());
    }

    public function register(Request $request) {
        $ip = $request->ip();

        $action = app(RegisterUser::class);
        $user = $action->execute($ip);

        return response()->json([
            'access_token' => $user->access_token,
            'token' => $this->jwtManager->generateToken(TokenPayload::from([
                'id' => $user->id,
                'ip_address' => $user->ip_address,
            ])),
        ]);
    }

    public function login(Request $request) {
        $accessToken = $request->input('access_token');

        /** @var User $user */
        $user = $this->userRepository->findBy(['access_token' => $accessToken]);

        if (! $user) {
            return response()->json([
                'message' => 'Invalid access token',
            ], 401);
        }

        $token = $this->jwtManager->generateToken(TokenPayload::from([
            'id' => $user->id,
            'ip_address' => $user->ip_address,
        ]));

        return response()->json($token);
    }
}
