<?php

namespace App\Filament\Resources\Student;

use App\Filament\Resources\Student\SupervisionResource\Pages;
use App\Filament\Resources\Student\SupervisionResource\RelationManagers;
use App\Models\Lecturer;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\IsDeleteScope;
use App\Models\Student\Supervision;
use App\Models\Thesis;
use App\Models\ThesisSupervision;
use App\Models\ThesisSupervisionFile;
use App\ThesisSupervisionStatus;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Blog\Post;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use Filament\Infolists\Components;


class SupervisionResource extends Resource
{
    protected static ?string $model = ThesisSupervision::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        $user = auth()->user(); // Ambil user yang sedang login
        return $user && $user->role && in_array($user->role->name, ['Mahasiswa', 'Dosen', 'Admin']);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user(); // Ambil user yang sedang login
        return $user && $user->role && in_array($user->role->name, ['Mahasiswa']);
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user(); // Ambil user yang sedang login
        return $user && $user->role && in_array($user->role->name, ['Mahasiswa']);
    }

    public static function canDelete(Model $record): bool
    {

        $user = auth()->user(); // Ambil user yang sedang login
        return $user && $user->role && in_array($user->role->name, ['Mahasiswa']);
    }

    public static function form(Form $form): Form
    {
        $idMahasiswa = auth()->user()->student_id;

        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Select::make('thesis_id')
                            ->label('Thesis')
                            ->options(collect(Thesis::where('student_id', $idMahasiswa)->get())
                                ->mapWithKeys(fn($record) => [$record->id => "{$record->title} - {$record->description}"]))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $lecturers = Lecturer::whereIn('id', function ($query) use ($state) {
                                    $query->selectRaw('lecturer_1_id FROM thesis WHERE id = ?', [$state])
                                        ->union(
                                            Thesis::selectRaw('lecturer_2_id')->where('id', $state)
                                        );
                                })->get();

                                // ->get()
                                // ->mapWithKeys(fn($lecturer) => [$lecturer->id => "{$lecturer->nip} - {$lecturer->name}"]);

                                // Mapping dengan label "Dosen 1 - Nama" dan "Dosen 2 - Nama"
                                $lecturerOptions = [];
                                foreach ($lecturers as $lecturer) {
                                    $thesis = Thesis::find($state);
                                    if ($thesis->lecturer_1_id === $lecturer->id) {
                                        $lecturerOptions[$lecturer->id] = "Dosen Pembimbing 1  -  {$lecturer->name}";
                                    } elseif ($thesis->lecturer_2_id === $lecturer->id) {
                                        $lecturerOptions[$lecturer->id] = "Dosen Pembimbing 2  -  {$lecturer->name}";
                                    }
                         }

                                // Log::info('Thesis ID Terpilih: ' . json_encode($lecturerOptions)); // 🔍 Cek nilai thesis_id
                                // Set daftar lecturer_id agar opsi lecturer berubah
                                $set('lecturer_id', null); // Reset nilai lecturer
                                $set('lecturer_options', array_reverse($lecturerOptions)); // Set daftar opsi lecturer
                            })
                            ->columnSpan(2),


                        Select::make('lecturer_id')
                            ->label('Lecturer')
                            ->reactive()
                            ->options(fn(callable $get) => $get('lecturer_options') ?? [])
                            ->searchable()
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('external_link')
                            ->label('External Link')
                            ->placeholder('Input External Link (Zoom, Google Meet, etc)')
                            ->minLength(2)
                            ->maxLength(255)
                            ->columnSpan(2),



                    ])
                    ->columns(2),

                Forms\Components\Section::make('File Upload')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->disk('public') // Penyimpanan di `storage/app/public`
                            ->directory('uploads/theses_supervision_files/' . now()->format('Y-m-d')) // Folder penyimpanan berdasarkan bulan dan tahun
                            ->preserveFilenames() // Menyimpan nama asli file
                            ->maxSize(2048) // Maksimum 2MB
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->placeholder('Drop your file here, file must be in PDF or Word format & maximum 2MB')
                            ->hiddenLabel()
                            ->required(),


                        Forms\Components\MarkdownEditor::make('description')
                        ->required()
                        ->placeholder('Input description about your supervision (method, schedule, etc)')
                        ->columnSpan('full'),
                    ])

                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => self::$model::query()->withoutGlobalScope(IsDeleteScope::class)->withoutGlobalScope(ActiveScope::class))


            ->columns([
                TextColumn::make('lecturer.name')
                    ->sortable()
                    ->searchable()
                    ->label('Lecturer'),

                TextColumn::make('description')
                    ->sortable()
                    ->searchable()
                    ->label('Description'),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => ThesisSupervisionStatus::tryFrom($state)?->label() ?? $state)
                    ->badge()
                    ->color(fn($state) => ThesisSupervisionStatus::tryFrom($state)?->color() ?? 'secondary'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime('d M Y, H:i'),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->sortable()
                    ->dateTime('d M Y, H:i'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSupervisions::route('/'),
            'create' => Pages\CreateSupervision::route('/create'),
            'edit' => Pages\EditSupervision::route('/{record}/edit'),
            'view' => Pages\ViewSupervision::route('/{record}'),

        ];
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        // Components\TextEntry::make('thesis.title')->label('Thesis Title'),
                                        Components\TextEntry::make('description')->label('Description'),
                                        Components\TextEntry::make('status')->label('Status')
                                        ->badge()
                                        ->color(fn($state) => ThesisSupervisionStatus::tryFrom($state)?->color() ?? 'gray'),
                                        Components\TextEntry::make('created_at')->label('Created At'),
                                        Components\TextEntry::make('file_path')
                                        ->label('Uploaded File')
                                        ->formatStateUsing(fn($state) => $state
                                            ? '<a href="'.asset('storage/'.$state).'" target="_blank" class="underline text-primary">Download File</a>'
                                            : 'No file uploaded')
                                        ->html(),



                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('student.name'),
                                        Components\TextEntry::make('lecturer.name'),
                                        Components\TextEntry::make('external_link')->label('External Link'),


                                    ]),
                                ]),
                            // Components\ImageEntry::make('image')
                            //     ->hiddenLabel()
                            //     ->grow(false),
                        ])->from('lg'),
                    ]),
                Components\Section::make('Content')
                    ->schema([
                        Components\TextEntry::make('content')
                            ->prose()
                            ->markdown()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),
            ]);
    }
}
