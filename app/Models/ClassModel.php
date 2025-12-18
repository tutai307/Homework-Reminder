<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClassModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'school_year',
        'description',
        'public_share_token',
        'public_share_slug',
    ];

    /**
     * Ensure the class has a public share token and return it.
     */
    public function ensurePublicShareToken(): string
    {
        if (!empty($this->public_share_token)) {
            return $this->public_share_token;
        }

        // 64 chars, URL-safe, reasonably unguessable
        $this->public_share_token = Str::random(64);
        $this->save();

        return $this->public_share_token;
    }

    /**
     * Ensure the class has a human-friendly share slug (non-secret).
     * Token remains the secret link; slug is for nicer URLs.
     */
    public function ensurePublicShareSlug(): string
    {
        if (!empty($this->public_share_slug)) {
            return $this->public_share_slug;
        }

        $base = Str::slug('homework-' . $this->id);
        $slug = $base;

        $counter = 1;
        while (static::where('public_share_slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        $this->public_share_slug = $slug;
        $this->save();

        return $this->public_share_slug;
    }

    /**
     * Get all timetables for this class.
     */
    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'class_id');
    }

    /**
     * Get all homework for this class.
     */
    public function homework()
    {
        return $this->hasMany(Homework::class, 'class_id');
    }

    /**
     * Get all tests for this class.
     */
    public function tests()
    {
        return $this->hasMany(Test::class, 'class_id');
    }

    /**
     * Get all users assigned to this class.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_classes', 'class_id', 'user_id')
            ->withTimestamps();
    }
}

