<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseBundleItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['bundle_id', 'course_id', 'sort_order'];

    public function bundle(): BelongsTo { return $this->belongsTo(Course::class, 'bundle_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class, 'course_id'); }
}
