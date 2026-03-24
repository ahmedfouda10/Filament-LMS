<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseShare extends Model
{
    public $timestamps = false;
    protected $fillable = ['course_id', 'user_id', 'platform', 'ip_address', 'created_at'];

    protected function casts(): array { return ['created_at' => 'datetime']; }

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
