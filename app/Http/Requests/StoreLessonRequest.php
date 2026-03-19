<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['sometimes', 'in:video,quiz,assignment,reading'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'is_free' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
