<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'bio', 'specialization', 'years_of_experience',
        'qualifications', 'education', 'expertise', 'social_links',
    ];

    protected function casts(): array
    {
        return [
            'qualifications' => 'array',
            'education' => 'array',
            'expertise' => 'array',
            'social_links' => 'array',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
