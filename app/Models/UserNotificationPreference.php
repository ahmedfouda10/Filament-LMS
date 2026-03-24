<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = ['user_id', 'course_updates', 'marketing', 'account_security'];

    protected function casts(): array
    {
        return ['course_updates' => 'boolean', 'marketing' => 'boolean', 'account_security' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
