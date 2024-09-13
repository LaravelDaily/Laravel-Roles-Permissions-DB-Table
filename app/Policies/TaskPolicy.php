<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function create(User $user): bool
    {
        return $user->role_id == Role::ROLE_ADMIN;
    }

    public function update(User $user, Task $task): bool
    {
        return in_array($user->role_id, [Role::ROLE_ADMIN, Role::ROLE_MANAGER])
            || $task->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->role_id == Role::ROLE_ADMIN;
    }
}
