<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
        ];

        // Instructor profile fields
        if ($this->user()?->role === 'instructor') {
            $rules = array_merge($rules, [
                'bio' => ['sometimes', 'nullable', 'string'],
                'specialization' => ['sometimes', 'nullable', 'string', 'max:255'],
                'years_of_experience' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'qualifications' => ['sometimes', 'nullable', 'array'],
                'qualifications.*' => ['string'],
                'education' => ['sometimes', 'nullable', 'array'],
                'education.*.degree' => ['required_with:education', 'string'],
                'education.*.institution' => ['required_with:education', 'string'],
                'education.*.year' => ['required_with:education', 'integer'],
                'expertise' => ['sometimes', 'nullable', 'array'],
                'expertise.*' => ['string'],
                'social_links' => ['sometimes', 'nullable', 'array'],
                'social_links.linkedin' => ['sometimes', 'nullable', 'string'],
                'social_links.twitter' => ['sometimes', 'nullable', 'string'],
                'social_links.website' => ['sometimes', 'nullable', 'string'],
            ]);
        }

        return $rules;
    }
}
