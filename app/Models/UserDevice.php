<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'device_name', 'browser', 'ip_address', 'location', 'last_active_at', 'is_current', 'token'];
    protected $hidden = ['token', 'ip_address'];

    protected function casts(): array
    {
        return ['last_active_at' => 'datetime', 'is_current' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
