<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'role', 'is_active',
        'title', 'email_verification_token', 'max_devices',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function instructorProfile()
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function lessonCompletions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function instructorTransactions()
    {
        return $this->hasMany(InstructorTransaction::class, 'instructor_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    // Phase 2 relationships
    public function devices() { return $this->hasMany(UserDevice::class); }
    public function lessonNotes() { return $this->hasMany(LessonNote::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function notifications() { return $this->hasMany(Notification::class); }
    public function notificationPreference() { return $this->hasOne(UserNotificationPreference::class); }
    public function videoProgress() { return $this->hasMany(VideoProgress::class); }
    public function refundRequests() { return $this->hasMany(RefundRequest::class); }
    public function promoCodeUsages() { return $this->hasMany(PromoCodeUsage::class); }

    // Phase 3 relationships
    public function preference() { return $this->hasOne(UserPreference::class); }
    public function offlineDownloads() { return $this->hasMany(OfflineDownload::class); }
    public function sentMessages() { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages() { return $this->hasMany(Message::class, 'receiver_id'); }
    public function courseShares() { return $this->hasMany(CourseShare::class); }
    public function installmentPlans() { return $this->hasMany(InstallmentPlan::class); }
    public function socialAccounts() { return $this->hasMany(SocialAccount::class); }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    // Scopes
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeInstructors($query)
    {
        return $query->where('role', 'instructor');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
