<?php

namespace App\Filament\Resources\Student\SupervisionResource\Pages;

use App\Filament\Resources\Student\SupervisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupervisions extends ListRecords
{
    protected static string $resource = SupervisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
