<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineDownload extends Model
{
    protected $fillable = ['user_id', 'course_id', 'lesson_id', 'file_size_bytes', 'download_token', 'expires_at', 'downloaded_at'];
    protected $hidden = ['download_token'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'downloaded_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function lesson(): BelongsTo { return $this->belongsTo(Lesson::class); }

    public function isExpired(): bool { return $this->expires_at->isPast(); }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
