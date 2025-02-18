<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThesisResource\Pages;
use App\Filament\Resources\ThesisResource\RelationManagers;
use App\Models\Lecturer;
use App\Models\Thesis;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\IsDeleteScope;
use App\Models\Student;
use App\ThesisStatus;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ThesisResource extends Resource
{
    protected static ?string $model = Thesis::class;
    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function canViewAny(): bool
    {
        $user = auth()->user(); // Ambil user yang sedang login
        return $user && $user->role && in_array($user->role->name, ['Admin', 'Dosen']);
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user(); // Ambil user yang sedang login
        return $user && $user->role && in_array($user->role->name, ['Admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([

                    Select::make('status')
                        ->label('Status')
                        ->options(collect(ThesisStatus::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])) // Ambil nilai dari enum
                        ->default(ThesisStatus::InProgress->value) // Set default ke "proses"
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('title')
                        ->label('Title')
                        ->placeholder('Input Title')
                        ->minLength(2)
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    TextInput::make('description')
                        ->label('Description')
                        ->placeholder('Input Description')
                        ->minLength(2)
                        // ->required()
                        ->maxLength(255)
                        ->columnSpan(2),


                    Select::make('student_id')
                        ->label('Student')
                        ->options(collect(Student::where('is_active', true)->whereNull('deleted_at')->get())->mapWithKeys(fn($record) => [$record->id =>"{$record->nim} - {$record->name}"]))
                        ->searchable()
                        ->required()
                        ->columnSpan(2),


                    Select::make('lecturer_1_id')
                        ->label('Lecturer 1')
                        ->options(collect(Lecturer::where('is_active', true)->whereNull('deleted_at')->get())->mapWithKeys(fn($record) => [$record->id =>"{$record->nip} - {$record->name}"]))
                        ->searchable()
                        ->required()
                        ->live() // Tambahkan live update agar validasi bisa dilakukan saat memilih
                        ->columnSpan(2)
                        ->different('lecturer_2_id'),

                    Select::make('lecturer_2_id')
                        ->label('Lecturer 2')
                        ->options(collect(Lecturer::where('is_active', true)->whereNull('deleted_at')->get())->mapWithKeys(fn($record) => [$record->id =>"{$record->nip} - {$record->name}"]))
                        ->searchable()
                        ->required()
                        ->live()
                        ->columnSpan(2)
                        ->different('lecturer_1_id'),





                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => self::$model::query()->withoutGlobalScope(IsDeleteScope::class)->withoutGlobalScope(ActiveScope::class))

            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->label('Title'),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => ThesisStatus::tryFrom($state)?->label() ?? $state)
                    ->badge()
                    ->color(fn($state) => ThesisStatus::tryFrom($state)?->color() ?? 'secondary'),


                TextColumn::make('student.name')
                    ->sortable()
                    ->searchable()
                    ->label('Student'),

                TextColumn::make('lecturer_1.name')
                    ->sortable()
                    ->searchable()
                    ->label('Lecturer 1'),

                TextColumn::make('lecturer_2.name')
                    ->sortable()
                    ->searchable()
                    ->label('Lecturer 2'),


                // BooleanColumn::make('is_active')
                //     ->label('Active'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime('d M Y, H:i'),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->sortable()
                    ->dateTime('d M Y, H:i'),
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime('d M Y, H:i') // Tampilkan "-" jika null
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ]),
                TrashedFilter::make(),
            ])


            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-s-arrow-path')
                    ->action(function ($record) {
                        $record->restore(); // Restore the soft-deleted record
                    })
                    ->visible(fn($record) => $record->trashed()), // Only show if the record is trashed

            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]); // Set is_active to true for selected records
                            }
                        })
                        ->icon('heroicon-s-check'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]); // Set is_active to false for selected records
                            }
                        })
                        ->icon('heroicon-s-x-circle'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTheses::route('/'),
            // 'create' => Pages\CreateThesis::route('/create'),
            // 'edit' => Pages\EditThesis::route('/{record}/edit'),
        ];
    }
}
