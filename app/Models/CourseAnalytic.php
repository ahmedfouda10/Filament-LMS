<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAnalytic extends Model
{
    public $timestamps = false;
    protected $fillable = ['course_id', 'date', 'views', 'unique_visitors', 'enrollments'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
