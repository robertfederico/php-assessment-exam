<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $taskId = $this->route('task')?->id;

        return [
            'title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tasks', 'title')
                    ->where('user_id', auth()->id())
                    ->ignore($taskId),
            ],
            'content' => 'required|string',
            'status' => ['required', Rule::in(TaskStatus::values())],
            'is_published' => 'sometimes|boolean',
            'image' => ['nullable', File::image()->max(4096)],
            'subtasks' => 'sometimes|array',
            'subtasks.*.id' => 'sometimes|string',
            'subtasks.*.title' => 'required_with:subtasks|string|max:255',
            'subtasks.*.completed' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.unique' => 'You already have a task with this title.',
            'image.max' => 'The image must not be larger than 4MB.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_published')) {
            $this->merge([
                'is_published' => filter_var($this->is_published, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            ]);
        }
    }
}
