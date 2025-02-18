<?php

namespace App\Filament\Resources\Student\SupervisionResource\Pages;

use App\Filament\Resources\Student\SupervisionResource;
use App\Models\Notification as ModelsNotification;
use App\Models\ThesisSupervision;
use App\Models\ThesisSupervisionFile;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewSupervision extends ViewRecord
{
    protected static string $resource = SupervisionResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Post */
        $record = $this->getRecord();

        return $record->thesis->title;
    }

    protected function getActions(): array
    {
        return [];
    }
}
