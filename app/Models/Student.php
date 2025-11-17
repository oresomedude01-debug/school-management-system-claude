<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'nationality',
        'religion',
        'blood_group',
        'phone',
        'email',
        'current_address',
        'permanent_address',
        'class_id',
        'section_id',
        'session_year',
        'admission_date',
        'admission_year',
        'admission_month',
        'admission_position_in_month',
        'admission_position_overall',
        'previous_school_name',
        'previous_school_address',
        'previous_class',
        'reason_for_transfer',
        'has_allergy',
        'allergy_type',
        'allergy_severity',
        'allergy_notes',
        'health_notes',
        'registration_token_id',
        'status',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'has_allergy' => 'boolean',
        'is_active' => 'boolean',
        'admission_year' => 'integer',
        'admission_month' => 'integer',
        'admission_position_in_month' => 'integer',
        'admission_position_overall' => 'integer',
    ];

    protected $appends = ['full_name'];

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    /**
     * Relationships
     */

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function registrationToken()
    {
        return $this->belongsTo(RegistrationToken::class);
    }

    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'parent_student')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeInClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeInSection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }
}
