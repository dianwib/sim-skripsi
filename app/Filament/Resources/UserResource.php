<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Lecturer;
use App\Models\Role;
use App\Models\User;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\IsDeleteScope;
use App\Models\Student;
use App\UserStatus;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

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
                    Select::make('role_id')
                        ->label('Role')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            $set('email', '');
                            $set('password', '');
                        })

                        ->searchable()
                        ->columnSpan(2)
                        ->options(collect(Role::all())->mapWithKeys(fn($role) => [$role->id => $role->name])),


                    Select::make('student_id')

                        ->label('Student')
                        ->disabled(fn($get) => $get('role_id') != Role::where('name', 'Mahasiswa')->first()->id)
                        ->options(collect(Student::where('is_active', true)->whereNull('deleted_at')->get())->mapWithKeys(fn($record) => [$record->id => "{$record->nim} - {$record->name}"]))
                        ->searchable()
                        ->hidden(fn($get) => $get('role_id') != Role::where('name', 'Mahasiswa')->first()->id)
                        ->afterStateUpdated(
                            fn($state, callable $set) =>
                            $set('username', Student::find($state)?->nim) // Mengatur username dari NIM
                        )->live()
                        ->columnSpan(2),

                    Select::make('lecturer_id')
                        ->label('Lecturer')
                        ->disabled(fn($get) => $get('role_id') != Role::where('name', 'Dosen')->first()->id)
                        ->options(collect(Lecturer::where('is_active', true)->whereNull('deleted_at')->get())->mapWithKeys(fn($record) => [$record->id => "{$record->nip} - {$record->name}"]))
                        ->searchable()
                        ->hidden(fn($get) => $get('role_id') != Role::where('name', 'Dosen')->first()->id)

                        ->columnSpan(2),

                    TextInput::make('username')
                        ->label('Username')
                        ->placeholder('Input Username')
                        ->minLength(2)
                        ->required()->hidden()
                        ->maxLength(255)
                        ->columnSpan(2),

                    TextInput::make('email')
                        ->email()
                        ->unique()
                        ->label('Email')
                        ->placeholder('Input Email')
                        ->minLength(2)
                        ->required()
                        ->maxLength(255)
                        // ->afterStateUpdated(
                        //     fn($state, callable $set, $get) =>
                        //     $get('role_id') == Role::where('name', 'Dosen')->first()?->id
                        //         ? $set('username', $state) // Jika role Dosen, username = email
                        //         : null
                        // )
                        ->live()
                        ->columnSpan(2),

                    TextInput::make('password')
                        ->label('Password')
                        ->placeholder('Input Password')
                        ->minLength(2)
                        ->required()
                        ->password()
                        ->maxLength(255)
                        ->columnSpan(2),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => self::$model::query()->withoutGlobalScope(IsDeleteScope::class)->withoutGlobalScope(ActiveScope::class))
            ->columns([
                TextColumn::make('username')
                    ->sortable()
                    ->searchable()
                    ->label('Username'),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name'),


                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->label('Email'),

                TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->role?->getBadgeColor() ?? 'gray')
                    ->searchable(),

                // TextColumn::make('student.name')
                //     ->sortable()
                //     ->searchable()
                //     ->label('Student'),

                // TextColumn::make('lecturer.name')
                //     ->sortable()
                //     ->searchable()
                //     ->label('Lecturer'),


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
                SelectFilter::make('role_id')
                    ->label('Role')
                    ->options(collect(Role::all())->mapWithKeys(fn($role) => [$role->id => $role->name])),
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
                    // Tables\Actions\BulkAction::make('activate')
                    //     ->label('Activate')
                    //     ->action(function ($records) {
                    //         foreach ($records as $record) {
                    //             $record->update(['is_active' => true]); // Set is_active to true for selected records
                    //         }
                    //     })
                    //     ->icon('heroicon-s-check'),

                    // Tables\Actions\BulkAction::make('deactivate')
                    //     ->label('Deactivate')
                    //     ->action(function ($records) {
                    //         foreach ($records as $record) {
                    //             $record->update(['is_active' => false]); // Set is_active to false for selected records
                    //         }
                    //     })
                    //     ->icon('heroicon-s-x-circle'),
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
