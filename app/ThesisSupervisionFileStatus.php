<?php

namespace App;

enum ThesisSupervisionFileStatus: string
{
    case Pending = 'pending';
    case Revised = 'revised';
    case Accepted = 'accepted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Revised => 'Direvisi',
            self::Accepted => 'Diterima',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning', // Kuning
            self::Revised => 'info', // Biru
            self::Accepted => 'success', // Hijau
        };
    }
}
