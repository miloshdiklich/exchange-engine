<?php


namespace App\Repositories;


use App\Contracts\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }
    
    public function lockById(int $id): ?User
    {
        return User::query()
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }
    
    public function save(User $user): void
    {
        $user->save();
    }
}
