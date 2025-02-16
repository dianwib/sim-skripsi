<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Scopes\IsDeleteScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUlids, SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'student_id',
        'lecturer_id',
        'role_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Jika role adalah Mahasiswa dan student_id diisi
            if ($user->role_id == Role::where('name', 'Mahasiswa')->first()?->id && $user->student_id) {
                $user->username = Student::find($user->student_id)?->nim;
            } else {
                $user->username = $user->email;
            }


            if ($user->role_id == Role::where('name', 'Mahasiswa')->first()?->id && $user->student_id) {
                $user->name = Student::find($user->student_id)?->name;
            } else if ($user->role_id == Role::where('name', 'Dosen')->first()?->id && $user->lecturer_id) {
                $user->name = Lecturer::find($user->lecturer_id)?->name;
            }

        });

        static::saving(function ($user) {
            // Jika role adalah Mahasiswa dan student_id diisi
            if ($user->role_id == Role::where('name', 'Mahasiswa')->first()?->id && $user->student_id) {
                $user->username = Student::find($user->student_id)?->nim;
            } else {
                $user->username = $user->email;
            }

            if ($user->role_id == Role::where('name', 'Mahasiswa')->first()?->id && $user->student_id) {
                $user->name = Student::find($user->student_id)?->name;
            } else if ($user->role_id == Role::where('name', 'Dosen')->first()?->id && $user->lecturer_id) {
                $user->name = Lecturer::find($user->lecturer_id)?->name;
            }
        });
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }







    protected static function booted()
    {
        // static::addGlobalScope(new IsDeleteScope());
    }
}
