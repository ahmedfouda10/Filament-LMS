<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'title' => $this->title ?? null,
            'profile' => new InstructorProfileResource($this->whenLoaded('instructorProfile')),
            'courses_count' => $this->courses()->published()->count(),
            'total_students' => $this->courses()->withCount('enrollments')->get()->sum('enrollments_count'),
            'average_rating' => round($this->courses()->with('reviews')->get()->flatMap->reviews->where('is_approved', true)->avg('rating') ?? 0, 1),
            'courses' => $this->when($this->relationLoaded('courses'), function () {
                return $this->courses->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'slug' => $c->slug,
                    'image' => $c->image,
                    'price' => (float) $c->price,
                    'original_price' => $c->original_price ? (float) $c->original_price : null,
                    'students_count' => $c->students_count,
                    'average_rating' => $c->average_rating,
                ]);
            }),
            'reviews' => $this->when($this->relationLoaded('courses'), function () {
                $courseIds = $this->courses->pluck('id');
                return Review::whereIn('course_id', $courseIds)
                    ->where('is_approved', true)
                    ->with(['user:id,name,avatar', 'course:id,title'])
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'user' => ['name' => $r->user->name, 'avatar' => $r->user->avatar],
                        'course' => ['title' => $r->course->title],
                        'rating' => $r->rating,
                        'comment' => $r->comment,
                        'created_at' => $r->created_at,
                    ]);
            }),
        ];
    }
}
