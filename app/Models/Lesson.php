<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id', 'title', 'type', 'duration_minutes', 'video_duration_seconds',
        'video_url', 'content', 'is_free', 'sort_order', 'views_count', 'thumbnail',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'duration_minutes' => 'integer',
        ];
    }

    // Relationships
    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function completions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    // Phase 2 relationships
    public function resources() { return $this->hasMany(LessonResource::class); }
    public function notes() { return $this->hasMany(LessonNote::class); }
    public function videoProgress() { return $this->hasMany(VideoProgress::class); }
}
