<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Filament\Resources\BudgetResource\RelationManagers;
use App\Models\Budget;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Field Category: Menggunakan Select untuk memilih kategori dari database
                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                // Field Amount: Menggunakan TextInput untuk jumlah budget dengan format mata uang
                TextInput::make('amount')
                    ->label('Budget Amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),

                // Field Tanggal: Menggunakan DatePicker untuk memilih rentang waktu budget
                DatePicker::make('start_date')
                    ->required(),

                DatePicker::make('end_date')
                    ->required(),

                // Field user_id disembunyikan karena akan diisi otomatis
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Menampilkan nama pengguna dan nama kategori, bukan ID
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                // Menampilkan jumlah budget dengan format mata uang
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable()
                    ->money('IDR'),

                TextColumn::make('start_date')
                    ->date(),
                TextColumn::make('end_date')
                    ->date(),

                // Kolom created_at dan updated_at
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tambahkan filter jika diperlukan di sini
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }

    // Metode ini memastikan hanya budget milik user yang sedang login yang ditampilkan
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    // Metode ini secara otomatis mengisi user_id saat budget baru dibuat
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
}
