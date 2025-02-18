<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LecturerResource\Pages;
use App\Filament\Resources\LecturerResource\RelationManagers;
use App\Models\Lecturer;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\IsDeleteScope;
use Filament\Forms;
use Filament\Forms\Components\Card;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LecturerResource extends Resource
{
    protected static ?string $model = Lecturer::class;

    protected static ?string $navigationGroup = 'Academic';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function canViewAny(): bool
    {
        $user = auth()->user(); // Ambil user yang sedang login
        return $user && $user->role && in_array($user->role->name, ['Admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    //
                    TextInput::make('name')
                        ->label('Name')
                        ->placeholder('Input Name')
                        ->minLength(2)
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    TextInput::make('nip')
                        ->label('NIP')
                        ->numeric()
                        ->placeholder('Input NIP')
                        ->minLength(2)
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Toggle::make('is_active')
                        ->label('Is Active?')
                        ->required()
                        ->default(true)
                        ->columnSpan(2),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => self::$model::query()->withoutGlobalScope(IsDeleteScope::class)->withoutGlobalScope(ActiveScope::class))

            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name'),

                TextColumn::make('nip')
                    ->sortable()
                    ->searchable()
                    ->label('NIP'),

                BooleanColumn::make('is_active')
                    ->label('Active'),

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
            'index' => Pages\ListLecturers::route('/'),
            // 'create' => Pages\CreateLecturer::route('/create'),
            // 'edit' => Pages\EditLecturer::route('/{record}/edit'),
        ];
    }
}
