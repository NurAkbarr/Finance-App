<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $modelLabel = 'Rekening';
    protected static ?string $pluralModelLabel = 'Daftar Rekening';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Rekening')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('initial_balance')
                    ->label('Saldo Awal')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix('Rp'),
                // user_id disembunyikan
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Rekening')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('initial_balance')
                    ->label('Saldo Awal')
                    ->money('IDR')
                    ->sortable(),
                // Kolom dinamis untuk saldo saat ini (akan dihitung nanti)
                // TextColumn::make('current_balance')->label('Saldo Saat Ini'),
            ])
            ->filters([
                //
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

    // Metode ini memastikan hanya akun milik user yang sedang login yang ditampilkan
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    // Metode ini secara otomatis mengisi user_id saat akun baru dibuat
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}