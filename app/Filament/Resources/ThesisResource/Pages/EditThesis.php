<?php

namespace App\Filament\Resources\ThesisResource\Pages;

use App\Filament\Resources\ThesisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditThesis extends EditRecord
{
    protected static string $resource = ThesisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
