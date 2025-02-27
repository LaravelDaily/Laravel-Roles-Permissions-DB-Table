<?php

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('allows administrator to access create task page', function () {
    $user = User::factory()
        ->create(['role_id' => Role::ROLE_ADMIN]);

    actingAs($user)
        ->get(route('tasks.create'))
        ->assertOk();
});

it('does not allow other users to access create task page', function (User $user) {
    actingAs($user)
        ->get(route('tasks.create'))
        ->assertForbidden();
})->with([
    fn() => User::factory()->create(['role_id' => Role::ROLE_USER]),
    fn() => User::factory()->create(['role_id' => Role::ROLE_MANAGER]),
]);

it('allows administrator and manager to enter update page for any task', function (User $user) {
    $task = Task::factory()->create(['user_id' => User::factory()->create()->id]);

    actingAs($user)
        ->get(route('tasks.edit', $task))
        ->assertOk();
})->with([
    fn() => User::factory()->create(['role_id' => Role::ROLE_ADMIN]),
    fn() => User::factory()->create(['role_id' => Role::ROLE_MANAGER]),
]);

it('allows administrator and manager to update any task', function (User $user) {
    $task = Task::factory()->create(['user_id' => User::factory()->create()->id]);

    actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => 'updated task name',
        ])
        ->assertRedirect();

    expect($task->refresh()->name)->toBe('updated task name');
})->with([
    fn() => User::factory()->create(['role_id' => Role::ROLE_ADMIN]),
    fn() => User::factory()->create(['role_id' => Role::ROLE_MANAGER]),
]);

it('allows user to update his own task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => 'updated task name',
        ]);

    expect($task->refresh()->name)->toBe('updated task name');
});

it('does no allow user to update other users task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => User::factory()->create()->id]);

    actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => 'updated task name',
        ])
        ->assertForbidden();
});

it('allows administrator to delete task', function () {
    $task = Task::factory()->create(['user_id' => User::factory()->create()->id]);
    $user = User::factory()
        ->create(['role_id' => Role::ROLE_ADMIN]);

    actingAs($user)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect();

    expect(Task::count())->toBe(0);
});

it('does not allow other users to delete tasks', function (User $user) {
    $task = Task::factory()->create(['user_id' => User::factory()->create()->id]);

    actingAs($user)
        ->delete(route('tasks.destroy', $task))
        ->assertForbidden();
})->with([
    fn() => User::factory()->create(['role_id' => Role::ROLE_USER]),
    fn() => User::factory()->create(['role_id' => Role::ROLE_MANAGER]),
]);
