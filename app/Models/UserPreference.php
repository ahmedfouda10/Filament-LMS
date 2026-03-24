<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = ['user_id', 'theme', 'language', 'direction'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
