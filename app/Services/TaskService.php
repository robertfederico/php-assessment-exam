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
        array $filters = [],
        int $perPage = 10,
        string $orderBy = 'created_at',
        string $orderDirection = 'desc'
    ): AnonymousResourceCollection {
        $query = auth()->user()->tasks();

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
    public function create(array $data): TaskResource
    {
        if (! empty($data['image'])) {
            $data['image_path'] = $this->handleImageUpload($data['image']);
            unset($data['image']);
        }

        $data = $this->checkForCompletedSubTasks($data);
        $task = auth()->user()
            ->tasks()
            ->create($data);

        return TaskResource::make($task);
    }

    /**
     * Check if subtasks are completed, set task status to done if true
     */
    private function checkForCompletedSubTasks(array $data): array
    {
        if (empty($data['subtasks'])) {
            return $data;
        }

        $incompleteSubTasks = collect($data['subtasks'])->where('completed', false)->count();

        if ($incompleteSubTasks === 0) {
            $data['status'] = TaskStatus::DONE;
        }

        return $data;
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
