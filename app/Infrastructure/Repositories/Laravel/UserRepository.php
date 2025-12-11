<?php

namespace App\Infrastructure\Repositories\Laravel;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User as UserModel;

class UserRepository extends CleanRepositoriesAbstract implements UserRepositoryInterface
{
    protected string $dataClass = User::class;

    public function __construct(UserModel $model)
    {
        parent::__construct($model);
    }
}
