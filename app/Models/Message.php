<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'course_id', 'subject', 'body', 'is_read', 'read_at'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean', 'read_at' => 'datetime'];
    }

    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'receiver_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }

    public function scopeUnread(Builder $query): Builder { return $query->where('is_read', false); }
    public function scopeInbox(Builder $query, int $userId): Builder { return $query->where('receiver_id', $userId); }
    public function scopeSent(Builder $query, int $userId): Builder { return $query->where('sender_id', $userId); }

    public function scopeConversationWith(Builder $query, int $userId, int $otherId): Builder
    {
        return $query->where(function ($q) use ($userId, $otherId) {
            $q->where('sender_id', $userId)->where('receiver_id', $otherId);
        })->orWhere(function ($q) use ($userId, $otherId) {
            $q->where('sender_id', $otherId)->where('receiver_id', $userId);
        });
    }

    public function markAsRead(): void { $this->update(['is_read' => true, 'read_at' => now()]); }
}
