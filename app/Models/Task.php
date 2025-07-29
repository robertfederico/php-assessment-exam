<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'status',
        'is_published',
        'image_path',
        'subtasks',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => TaskStatus::class,
        'is_published' => 'boolean',
        'subtasks' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Auto-update task status if all subtasks are completed
        static::updating(function (Task $task) {
            if ($task->isDirty('subtasks') && $task->allSubtasksCompleted()) {
                $task->status = TaskStatus::DONE;
            }
        });

        // Delete associated image when task is deleted
        static::deleting(function (Task $task) {
            if ($task->image_path) {
                Storage::delete($task->image_path);
            }
        });
    }

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL for the task image.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Check if task has subtasks.
     */
    public function hasSubtasks(): bool
    {
        return ! empty($this->subtasks);
    }

    /**
     * Get completed subtasks count.
     */
    public function getCompletedSubtasksCountAttribute(): int
    {
        if (! $this->hasSubtasks()) {
            return 0;
        }

        return collect($this->subtasks)->where('completed', true)->count();
    }

    /**
     * Get total subtasks count.
     */
    public function getTotalSubtasksCountAttribute(): int
    {
        return $this->hasSubtasks() ? count($this->subtasks) : 0;
    }

    /**
     * Get subtasks completion percentage.
     */
    public function getSubtasksProgressAttribute(): float
    {
        if (! $this->hasSubtasks()) {
            return 0;
        }

        return ($this->completed_subtasks_count / $this->total_subtasks_count) * 100;
    }

    /**
     * Check if all subtasks are completed.
     */
    public function allSubtasksCompleted(): bool
    {
        if (! $this->hasSubtasks()) {
            return false;
        }

        return $this->completed_subtasks_count === $this->total_subtasks_count;
    }

    /**
     * Scope to filter tasks by status.
     */
    public function scopeByStatus(Builder $query, TaskStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter published tasks.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to filter draft tasks.
     */
    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('is_published', false);
    }

    /**
     * Scope to search tasks by title.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('title', 'LIKE', "%{$search}%");
    }

    /**
     * Scope to order tasks by title.
     */
    public function scopeOrderByTitle(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('title', $direction);
    }

    /**
     * Scope to order tasks by creation date.
     */
    public function scopeOrderByDate(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('created_at', $direction);
    }

    /**
     * Scope to limit tasks to current user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
