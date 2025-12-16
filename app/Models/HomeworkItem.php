<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'homework_id',
        'subject_id',
        'content',
        'due_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get the homework that owns the homework item.
     */
    public function homework()
    {
        return $this->belongsTo(Homework::class, 'homework_id');
    }

    /**
     * Get the subject for the homework item.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}

