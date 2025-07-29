<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'is_published' => $this->is_published,
            'image_url' => $this->image_url,
            'subtasks' => $this->subtasks,
            'subtasks_progress' => $this->subtasks_progress,
            'total_subtasks_count' => $this->total_subtasks_count,
            'completed_subtasks_count' => $this->completed_subtasks_count,
            'has_subtasks' => $this->hasSubtasks(),
            'all_subtasks_completed' => $this->allSubtasksCompleted(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->when($this->deleted_at, fn () => $this->deleted_at->format('Y-m-d H:i:s')),
        ];
    }
}
