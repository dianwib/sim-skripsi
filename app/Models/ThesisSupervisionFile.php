<?php

namespace App\Models;

use App\ThesisSupervisionFileStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ThesisSupervisionFile extends Model
{

    use HasFactory, Notifiable, HasUlids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'thesis_supervision_files';

    protected $fillable = [
        'external_link',
        'description',
        'status',
        'file_path',
        'file_name',
        'thesis_supervision_id',
        'thesis_supervision_file_id',
        'previous_thesis_supervision_file_id',
        'is_revision',
        'revision_number',
    ];

    public function thesisSupervision()
    {
        return $this->belongsTo(ThesisSupervision::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($thesis) {
            $thesis->is_revision = $thesis->is_revision ?? false;
            $thesis->status = $thesis->status ?? ThesisSupervisionFileStatus::Pending->value;
        });
    }
}
