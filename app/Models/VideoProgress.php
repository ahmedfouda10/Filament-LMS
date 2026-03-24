<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    public $timestamps = false;
    const UPDATED_AT = 'updated_at';
    protected $table = 'video_progress';
    protected $fillable = ['user_id', 'lesson_id', 'progress_percentage', 'last_position_seconds', 'updated_at'];

    protected function casts(): array
    {
        return ['progress_percentage' => 'decimal:2', 'updated_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lesson(): BelongsTo { return $this->belongsTo(Lesson::class); }
}
