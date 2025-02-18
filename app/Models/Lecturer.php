<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Lecturer extends Model
{
    use HasFactory, Notifiable, HasUlids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nip',
        'is_active',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'lecturer_id');
    }

    public function theses()
    {
        return $this->hasMany(Thesis::class, 'lecturer_1_id')
                    ->orWhere('lecturer_2_id', $this->id);
    }

    public function thesisSupervision()
    {
        return $this->hasMany(ThesisSupervision::class, 'lecturer_id');
    }

    protected static function booted()
    {
        // static::addGlobalScope(new ActiveScope());
        // static::addGlobalScope(new IsDeleteScope());
    }
}
