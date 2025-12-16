<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get all homework created by this user.
     */
    public function homework()
    {
        return $this->hasMany(Homework::class, 'created_by');
    }

    /**
     * Get all tests created by this user.
     */
    public function tests()
    {
        return $this->hasMany(Test::class, 'created_by');
    }

    /**
     * Get all classes assigned to this user.
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'user_classes', 'user_id', 'class_id')
            ->withTimestamps();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    /**
     * Check if user is teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher' || $this->hasRole('teacher');
    }

    /**
     * Check if user is class monitor.
     */
    public function isClassMonitor(): bool
    {
        return $this->role === 'class_monitor' || $this->hasRole('class_monitor');
    }

    /**
     * Check if user can create homework.
     */
    public function canCreateHomework(): bool
    {
        return $this->isAdmin() || $this->isTeacher() || $this->isClassMonitor();
    }

    /**
     * Check if user can create timetable.
     */
    public function canCreateTimetable(): bool
    {
        return $this->isAdmin() || $this->isTeacher();
    }

    /**
     * Check if user has access to a class.
     */
    public function hasAccessToClass($classId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->classes()->where('classes.id', $classId)->exists();
    }

    /**
     * Get the class assigned to this teacher (only one class per teacher).
     */
    public function getAssignedClass()
    {
        if ($this->isAdmin()) {
            return null;
        }
        
        return $this->classes()->first();
    }
}
