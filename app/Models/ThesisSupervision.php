<?php

namespace App\Models;

use App\ThesisSupervisionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ThesisSupervision extends Model
{
    use HasFactory, Notifiable, HasUlids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'thesis_supervisions';

    protected $fillable = [
        'external_link',
        'description',
        'status',
        'student_id',
        'thesis_id',
        'lecturer_id',
        'count_revision',
        'file_path',
        'file_name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($thesis) {
            $thesis->count_revision = $thesis->count_revision ?? 0;
            $thesis->status = $thesis->status ?? ThesisSupervisionStatus::Pending->value;
            $thesis->student_id = $thesis->student_id ?? auth()->user()->student_id;
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }
}
