<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

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

