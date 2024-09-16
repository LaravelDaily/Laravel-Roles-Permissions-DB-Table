<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('Administrator');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['Administrator', 'Manager'])
            || $task->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole('Administrator');
    }
}
