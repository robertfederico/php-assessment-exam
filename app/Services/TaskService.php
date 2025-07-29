<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskService
{
    /**
     * Get paginated tasks for a user with optional filters.
     */
    public function getAllForUser(
        int $userId,
        array $filters = [],
        int $perPage = 10,
        string $orderBy = 'created_at',
        string $orderDirection = 'desc'
    ): AnonymousResourceCollection {
        $query = Task::forUser($userId);

        // Apply filters
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->byStatus(TaskStatus::from($filters['status']));
        }

        if (isset($filters['is_published']) && $filters['is_published'] !== 'all') {
            $isPublished = filter_var($filters['is_published'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_published', $isPublished);
        }

        // Apply ordering
        if ($orderBy === 'title') {
            $query->orderByTitle($orderDirection);
        } else {
            $query->orderByDate($orderDirection);
        }

        $tasks = $query->paginate($perPage)->withQueryString();

        return TaskResource::collection($tasks);
    }

    /**
     * Create a new task and return resource.
     */
    public function create(int $userId, array $data): TaskResource
    {
        if (! empty($data['image'])) {
            $data['image_path'] = $this->handleImageUpload($data['image']);
            unset($data['image']);
        }

        $data['user_id'] = $userId;
        $task = Task::create($data);

        return TaskResource::make($task);
    }

    /**
     * Update an existing task and return resource.
     */
    public function update(Task $task, array $data): TaskResource
    {
        if (! empty($data['image'])) {
            if ($task->image_path) {
                $this->deleteImage($task->image_path);
            }

            $data['image_path'] = $this->handleImageUpload($data['image']);
            unset($data['image']);
        }

        $task->update($data);

        return TaskResource::make($task->fresh());
    }

    /**
     * Delete a task and cleanup associated files.
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Update task status and return resource.
     */
    public function updateStatus(Task $task, TaskStatus $status): TaskResource
    {
        $task->update(['status' => $status]);

        return TaskResource::make($task->fresh());
    }

    /**
     * Toggle task published status and return resource.
     */
    public function togglePublished(Task $task): TaskResource
    {
        $task->update(['is_published' => ! $task->is_published]);

        return TaskResource::make($task->fresh());
    }

    /**
     * Add or update subtasks.
     *
     * @return TaskResource;
     */
    public function updateSubtasks(Task $task, array $subtasks): TaskResource
    {
        $formattedSubtasks = collect($subtasks)->map(function ($subtask) {
            return [
                'id' => $subtask['id'] ?? Str::uuid(),
                'title' => $subtask['title'],
                'completed' => $subtask['completed'] ?? false,
                'created_at' => $subtask['created_at'] ?? now()->toDateTimeString(),
            ];
        })->toArray();

        $task->update(['subtasks' => $formattedSubtasks]);

        return TaskResource::make($task->fresh());
    }

    /**
     * Mark subtask as completed/incomplete and return resource.
     */
    public function toggleSubtask(Task $task, string $subtaskId): TaskResource
    {
        $subtasks = collect($task->subtasks);

        $updatedSubtasks = $subtasks->map(function ($subtask) use ($subtaskId) {
            if ($subtask['id'] === $subtaskId) {
                $subtask['completed'] = ! $subtask['completed'];
            }

            return $subtask;
        })->toArray();

        $task->update(['subtasks' => $updatedSubtasks]);

        return TaskResource::make($task->fresh());
    }

    /**
     * Get trashed tasks for a user.
     */
    public function getTrashedForUser(int $userId, int $perPage = 10): TaskResource
    {
        $tasks = Task::forUser($userId)
            ->onlyTrashed()
            ->orderByDate()
            ->paginate($perPage);

        return TaskResource::make($tasks);
    }

    /**
     * Restore a trashed task and return resource.
     */
    public function restore(int $taskId, int $userId): ?TaskResource
    {
        $task = Task::forUser($userId)->onlyTrashed()->find($taskId);

        if ($task) {
            $task->restore();

            return TaskResource::make($task->fresh());
        }

        return null;
    }

    /**
     * Permanently delete a task.
     */
    public function forceDelete(int $taskId, int $userId): bool
    {
        $task = Task::forUser($userId)->onlyTrashed()->find($taskId);

        if ($task) {
            return $task->forceDelete();
        }

        return false;
    }

    /**
     * Handle image upload and return storage path.
     */
    private function handleImageUpload(UploadedFile $image): string
    {
        $filename = Str::uuid().'.'.$image->getClientOriginalExtension();

        return $image->storeAs('tasks', $filename, 'public');
    }

    /**
     * Delete an image file from storage.
     */
    private function deleteImage(string $imagePath): bool
    {
        return Storage::disk('public')->delete($imagePath);
    }
}
