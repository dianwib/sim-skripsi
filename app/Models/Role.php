<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Role extends Model
{
    use HasFactory, Notifiable, HasUlids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public function getBadgeColor(): string
    {
        return match ($this->name) {
            'Admin' => 'danger',  // Merah
            'Mahasiswa' => 'primary',  // Biru
            'Dosen' => 'success', // Hijau
            default => 'gray', // Default abu-abu
        };
    }

    protected static function booted()
    {
        // static::addGlobalScope(new ActiveScope());
        // static::addGlobalScope(new IsDeleteScope());
    }
}
