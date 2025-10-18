<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Get;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model; // Tambahkan ini jika belum ada

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        // Mendapatkan Rekening milik user yang sedang login
        $userAccounts = Account::where('user_id', Auth::id())->pluck('name', 'id');

        // Mendapatkan Kategori milik user (atau kategori umum jika Anda mengizinkan)
        $userCategories = Category::where('user_id', Auth::id())->orWhereNull('user_id')->pluck('name', 'id');

        return $form
            ->schema([
                Toggle::make('is_transfer')
                    ->label('Apakah ini Transfer Dana?')
                    ->live(),

                // Bagian untuk Transaksi Biasa (Pemasukan/Pengeluaran)
                Group::make()
                    ->schema([
                        Select::make('account_id')
                            ->label('Rekening')
                            ->options($userAccounts)
                            ->required(),
                        Select::make('category_id')
                            ->label('Category')
                            ->options($userCategories)
                            ->searchable()
                            ->required(),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('description')
                            ->maxLength(255)
                            ->default(null),
                        DatePicker::make('date')
                            ->default(Carbon::now())
                            ->required(),
                    ])
                    ->hidden(fn (Get $get): bool => $get('is_transfer')),

                // Bagian untuk Transfer Dana
                Fieldset::make('Detail Transfer')
                    ->schema([
                        Select::make('account_id_source')
                            ->label('Rekening Sumber (Dikurangi)')
                            ->options($userAccounts)
                            ->required(),
                        Select::make('transfer_target_id')
                            ->label('Rekening Tujuan (Ditambah)')
                            ->options($userAccounts)
                            ->required()
                            ->notIn([fn (Get $get) => $get('account_id_source')])
                            ->validationMessages([
                                'notIn' => 'Rekening tujuan tidak boleh sama dengan Rekening Sumber.',
                            ]),
                        TextInput::make('transfer_amount')
                            ->label('Jumlah Transfer')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        DatePicker::make('transfer_date')
                            ->label('Tanggal Transfer')
                            ->default(Carbon::now())
                            ->required(),
                    ])
                    ->hidden(fn (Get $get): bool => !$get('is_transfer')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Rekening')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->color(fn (Transaction $record) => $record->is_transfer ? 'gray' : 'primary'),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->description(function (Transaction $record) {
                        if ($record->is_transfer) {
                            $targetName = $record->transferTarget ? $record->transferTarget->name : 'N/A';
                            return $record->amount > 0 ? 'Transfer Masuk dari ' . $targetName : 'Transfer Keluar ke ' . $targetName;
                        }
                        return null;
                    }),

                TextColumn::make('amount')
                    ->numeric()
                    ->sortable()
                    ->money('IDR')
                    ->color(fn (float $state) => match (true) {
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                // Filter Berdasarkan Tanggal
                Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date))
                            ->when($data['to'], fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date));
                    }),

                // Filter Berdasarkan Tipe Kategori (Pemasukan/Pengeluaran)
                SelectFilter::make('category_type')
                    ->label('Tipe')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value'] !== null) {
                            return $query->whereHas('category', fn (Builder $q) => $q->where('type', $data['value']));
                        }
                        return $query;
                    }),

                // Filter Berdasarkan Nama Kategori
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        // Logika untuk TRANSFER DANA (Membuat DUA Transaksi)
        if (isset($data['is_transfer']) && $data['is_transfer']) {
            $transferAmount = $data['transfer_amount'];
            $targetAccount = Account::find($data['transfer_target_id']);
            $sourceAccount = Account::find($data['account_id_source']);

            // WAJIB: Ganti ini dengan ID kategori yang Anda buat untuk Transfer (misal ID 1, 2, dst)
            // Anda perlu memeriksa di database Anda, kategori mana yang akan digunakan untuk transfer.
            $transferCategoryId = 1;

            // 1. Transaksi Pengeluaran (DARI Rekening Sumber)
            Transaction::create([
                'user_id' => Auth::id(),
                'account_id' => $data['account_id_source'],
                'category_id' => $transferCategoryId,
                'amount' => -$transferAmount,
                'description' => 'Transfer keluar ke ' . $targetAccount->name,
                'date' => $data['transfer_date'],
                'is_transfer' => true,
                'transfer_target_id' => $data['transfer_target_id'],
            ]);

            // 2. Transaksi Pemasukan (KE Rekening Tujuan)
            Transaction::create([
                'user_id' => Auth::id(),
                'account_id' => $data['transfer_target_id'],
                'category_id' => $transferCategoryId,
                'amount' => $transferAmount,
                'description' => 'Transfer masuk dari ' . $sourceAccount->name,
                'date' => $data['transfer_date'],
                'is_transfer' => true,
                'transfer_target_id' => $data['account_id_source'],
            ]);

            // Mengembalikan array kosong agar proses penyimpanan standar Filament di CreateTransaction diabaikan
            return [];
        }

        // Logika untuk Transaksi Biasa (Pemasukan/Pengeluaran)
        // Cek kategori untuk menentukan apakah ini pengeluaran (harus bernilai negatif)
        $category = Category::find($data['category_id']);
        if ($category && $category->type == 'pengeluaran' && $data['amount'] > 0) {
            $data['amount'] = -$data['amount'];
        }

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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
