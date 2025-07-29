<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskFilterRequest extends FormRequest
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
        return [
            'search' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(TaskStatus::values())],
            'is_published' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
            'order_by' => 'nullable|in:created_at,title,updated_at',
            'order_direction' => 'nullable|in:asc,desc',
        ];
    }
}
