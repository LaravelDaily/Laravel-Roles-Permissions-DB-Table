<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'due_date', 'user_id', 'team_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (auth()->check()) {
                $task->team_id = auth()->user()->team_id;
            }
        });

        static::addGlobalScope('team-tasks', function (Builder $query) {
            if (auth()->check()) {
                $query->where('team_id', auth()->user()->team_id);
            }
        });
    }
}
