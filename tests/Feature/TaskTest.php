<?php

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('allows administrator to access create task page', function () {
    $admin = User::factory()
        ->create(['role_id' => Role::ROLE_ADMIN]);

    actingAs($admin)
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

it('creates task with the correct team', function () {
    $admin = User::factory()
        ->create(['role_id' => Role::ROLE_ADMIN]);

    actingAs($admin)
        ->post(route('tasks.store', [
            'name' => 'created by admin',
        ]))
        ->assertRedirect();

    $task = Task::latest()->first();
    expect($task->team_id)->toBe($admin->team_id);
});

it('allows to access task edit page for same team but not different team', function () {
    $admin = User::factory()->create(['role_id' => Role::ROLE_ADMIN]);
    $manager = User::factory()->create(['role_id' => Role::ROLE_MANAGER, 'team_id' => $admin->team_id]);
    $adminFromOtherTeam = User::factory()->create(['role_id' => Role::ROLE_ADMIN]);
    $task = Task::factory()->create(['user_id' => $admin->id, 'team_id' => $admin->team_id]);

    actingAs($admin)
        ->get(route('tasks.edit', $task))
        ->assertOk();

    actingAs($manager)
        ->get(route('tasks.edit', $task))
        ->assertOk();

    actingAs($adminFromOtherTeam)
        ->get(route('tasks.edit', $task))
        ->assertNotFound();
});

it('allows task update for same team but not different team', function () {
    $admin = User::factory()->create(['role_id' => Role::ROLE_ADMIN]);
    $manager = User::factory()->create(['role_id' => Role::ROLE_MANAGER, 'team_id' => $admin->team_id]);
    $adminFromOtherTeam = User::factory()->create(['role_id' => Role::ROLE_ADMIN]);
    $task = Task::factory()->create(['user_id' => $admin->id, 'team_id' => $admin->team_id]);

    actingAs($admin)
        ->put(route('tasks.update', $task), [
            'name' => 'updated by admin',
        ])
        ->assertRedirect();

    expect($task->refresh()->name)->toBe('updated by admin');

    actingAs($manager)
        ->put(route('tasks.update', $task), [
            'name' => 'updated by manager',
        ])
        ->assertRedirect();

    expect($task->refresh()->name)->toBe('updated by manager');

    actingAs($adminFromOtherTeam)
        ->put(route('tasks.update', $task), [
            'name' => 'updated by admin from another team',
        ])
        ->assertNotFound();

    expect($task->refresh()->name)->toBe('updated by manager');
});

it('allows user to update their own task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => 'updated task name',
        ]);

    expect($task->refresh()->name)->toBe('updated task name');
});

it('allows administrator to delete task', function () {
    $admin = User::factory()->create(['role_id' => Role::ROLE_ADMIN]);
    $task = Task::factory()->create(['user_id' => $admin->id, 'team_id' => $admin->team_id]);

    actingAs($admin)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect();

    expect(Task::count())->toBe(0);
});

it('does not allow other users to delete tasks', function () {
    $admin = User::factory()->create(['role_id' => Role::ROLE_ADMIN]);
    $task = Task::factory()->create(['user_id' => $admin->id, 'team_id' => $admin->team_id]);
    $user = User::factory()->create(['team_id' => $admin->team_id]);

    actingAs($user)
        ->delete(route('tasks.destroy', $task))
        ->assertForbidden();
});
