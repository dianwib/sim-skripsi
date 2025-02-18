<?php

namespace App\Filament\Resources\Student\SupervisionResource\Pages;

use App\Filament\Resources\Student\SupervisionResource;
use App\Models\Notification as ModelsNotification;
use App\Models\ThesisSupervision;
use App\Models\ThesisSupervisionFile;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSupervision extends CreateRecord
{
    protected static string $resource = SupervisionResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $user = auth()->user();


        $thesisId = $record->id;
        // $file = $this->form->getState()['file'];
        // Mengecek jika ada file revisi yang di-upload
        // if ($file) {
        //     ThesisSupervisionFile::create([
        //         'thesis_supervision_id' => $thesisId,
        //         'is_revision' => false,
        //         'file_path' => $file,
        //         'description' => $this->form->getState()['description_file'] ?? null,
        //     ]);
        // }



        Notification::make()
            // ->title('New supervision')
            // ->icon('heroicon-o-shopping-bag')
            // ->body("**{$record->student->name} submit supervision.**")
            ->actions([
                Action::make('View')
                    ->url(SupervisionResource::getUrl('edit', ['record' => $record])),
            ]);
            // ->sendToDatabase($user);


    }
}
