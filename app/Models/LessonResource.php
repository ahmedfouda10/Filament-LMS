<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonResource extends Model
{
    protected $fillable = ['lesson_id', 'title', 'file_url', 'file_type', 'file_size', 'is_downloadable', 'sort_order'];

    protected function casts(): array
    {
        return ['is_downloadable' => 'boolean'];
    }

    public function lesson(): BelongsTo { return $this->belongsTo(Lesson::class); }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
