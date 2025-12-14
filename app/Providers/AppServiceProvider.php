<?php

namespace App\Providers;

use App\Domain\Contracts\JwtManagerInterface;
use App\Infrastructure\Auth\LcobucciJwtManager;
use App\Infrastructure\Repositories\Laravel\UserRepository;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\ProjectRepositoryInterface;
use App\Domain\Repositories\ProjectNoteRepositoryInterface;
use App\Domain\Repositories\ProjectTimeRepositoryInterface;
use App\Infrastructure\Repositories\Laravel\ProjectNoteRepository;
use App\Infrastructure\Repositories\Laravel\ProjectRepository;
use App\Infrastructure\Repositories\Laravel\ProjectTimeRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(JwtManagerInterface::class, LcobucciJwtManager::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->singleton(ProjectNoteRepositoryInterface::class, ProjectNoteRepository::class);
        $this->app->singleton(ProjectTimeRepositoryInterface::class, ProjectTimeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::viaRequest('jwt', function (Request $request) {

            // 1. Lấy token
            $token = $request->bearerToken();
            if (!$token) return null;

            try {
                $jwtManager = app(JwtManagerInterface::class);
                $userRepo = app(UserRepositoryInterface::class);

                $payload = $jwtManager->parseToken($token);
                if (!$payload) return null;

                return $userRepo->findById($payload->id);

            } catch (\Exception $e) {
                return null;
            }
        });
    }
}
