<?php

namespace App;

enum ThesisSupervisionStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::InProgress => 'Proses',
            self::Completed => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning', // Kuning
            self::InProgress => 'info', // Biru
            self::Completed => 'success', // Hijau
        };
    }
}
