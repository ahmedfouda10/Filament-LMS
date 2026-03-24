<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'level' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'language' => ['sometimes', 'string', 'max:100'],
            'is_bundle' => ['sometimes', 'boolean'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['string'],
            'learning_outcomes' => ['nullable', 'array'],
            'learning_outcomes.*' => ['string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ];
    }
}
