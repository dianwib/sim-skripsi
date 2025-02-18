<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionnaireResource\Pages;
use App\Filament\Resources\QuestionnaireResource\RelationManagers;
use App\Models\Questionnaire;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
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

class QuestionnaireResource extends Resource
{
    protected static ?string $model = Questionnaire::class;
    protected static ?string $navigationGroup = 'Utility';

    protected static ?string $navigationIcon = 'heroicon-o-pencil';

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
                    Tabs::make('Tabs')
                        ->tabs([
                            Tab::make('Questionnaire')
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Title')
                                        ->placeholder('Input Questionnaire Title')
                                        ->minLength(2)
                                        ->required()
                                        ->columnSpan(2)
                                        ->maxLength(255),

                                    TextInput::make('description')
                                        ->label('Description')
                                        ->placeholder('Input Questionnaire Description')
                                        ->minLength(2)
                                        // ->required()
                                        ->columnSpan(2)
                                        ->maxLength(255),

                                    Toggle::make('is_published')
                                        ->label('Is Published?')
                                        ->required()
                                        ->default(true)
                                        ->columnSpan(2),

                                ]),

                            Tab::make('Questions')
                            ->schema([
                                Repeater::make('questions')
                                    ->relationship('questions')
                                    ->schema([
                                        TextInput::make('question')
                                            ->required()
                                            ->columnSpan(2),
                                    ])
                                    ->columns(2)
                                    ->createItemButtonLabel('Add Question')
                                    ->deletable(),
                            ]),


                        ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')
                ->sortable()
                ->searchable()
                ->label('Title'),

            // Tables\Columns\TextColumn::make('description')
            //     ->sortable()
            //     ->searchable()
            //     ->label('Description'),

            BooleanColumn::make('is_published')
                ->label('Published'),

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
                SelectFilter::make('is_published')
                    ->label('Status')
                    ->options([
                        true => 'Published',
                        false => 'Inpublished',
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
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_published' => true]); // Set is_published to true for selected records
                            }
                        })
                        ->icon('heroicon-s-check'),

                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_published' => false]); // Set is_published to false for selected records
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionnaires::route('/'),
            'create' => Pages\CreateQuestionnaire::route('/create'),
            'edit' => Pages\EditQuestionnaire::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
