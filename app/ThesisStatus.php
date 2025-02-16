<?php

namespace App;

enum ThesisStatus: string {
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'Proses',
            self::Completed => 'Selesai',
        };
    }
}
