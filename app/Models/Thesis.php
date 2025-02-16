<?php

namespace App\Models;

use App\ThesisStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Thesis extends Model
{
    use HasFactory, Notifiable, HasUlids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

     protected $table = 'thesis';

    protected $fillable = [
        'title',
        'description',
        'status',
        'student_id',
        'lecturer_1_id',
        'lecturer_2_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lecturer_1()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function lecturer_2()
    {
        return $this->belongsTo(Lecturer::class);
    }



    protected static function booted()
    {
        // static::addGlobalScope(new ActiveScope());
        // static::addGlobalScope(new IsDeleteScope());
    }
}
