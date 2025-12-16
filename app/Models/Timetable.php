<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'class_id',
        'weekday',
        'subject_id',
        'period',
    ];

    /**
     * Get the class that owns the timetable.
     */
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the subject for the timetable.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}

