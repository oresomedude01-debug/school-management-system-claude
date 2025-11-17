<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'exam_type',
        'marks_obtained',
        'total_marks',
        'grade',
        'remarks',
        'exam_date',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function getPercentageAttribute()
    {
        return ($this->marks_obtained / $this->total_marks) * 100;
    }
}
