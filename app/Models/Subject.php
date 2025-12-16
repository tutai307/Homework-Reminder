<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get all timetables for this subject.
     */
    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'subject_id');
    }

    /**
     * Get all homework items for this subject.
     */
    public function homeworkItems()
    {
        return $this->hasMany(HomeworkItem::class, 'subject_id');
    }

    /**
     * Get all tests for this subject.
     */
    public function tests()
    {
        return $this->hasMany(Test::class, 'subject_id');
    }
}

