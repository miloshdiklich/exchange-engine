<?php

namespace App\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id):? User;
    
    public function lockById(int $id): ?User;
    
    public function save(User $user): void;
}
