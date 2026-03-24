<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Course extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'instructor_id', 'category_id', 'title', 'slug', 'short_description',
        'description', 'image', 'level',
        'language', 'requirements', 'learning_outcomes', 'tags',
        'price', 'original_price', 'is_bundle', 'is_published',
        'is_featured', 'badge_text', 'badge_color', 'published_at', 'preview_video_url',
        'total_duration_minutes', 'students_count_cached', 'average_rating_cached',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'learning_outcomes' => 'array',
            'tags' => 'array',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'is_bundle' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    // Relationships
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // Computed attributes
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getStudentsCountAttribute(): int
    {
        return $this->enrollments()->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->modules()->with('lessons')->get()
            ->flatMap->lessons->sum('duration_minutes');
    }

    public function getTotalLessonsCount(): int
    {
        return $this->modules()->withCount('lessons')->get()->sum('lessons_count');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
    }

    // Phase 2 relationships
    public function bundleItems() { return $this->hasMany(CourseBundleItem::class, 'bundle_id'); }
    public function bundledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_bundle_items', 'bundle_id', 'course_id')
                    ->withPivot('sort_order')
                    ->orderByPivot('sort_order');
    }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function analytics() { return $this->hasMany(CourseAnalytic::class); }

    // Phase 3 relationships
    public function offlineDownloads() { return $this->hasMany(OfflineDownload::class); }
    public function shares() { return $this->hasMany(CourseShare::class); }
    public function messages() { return $this->hasMany(Message::class); }
}
