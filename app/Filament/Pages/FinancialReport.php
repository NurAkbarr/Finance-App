<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Transaction;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Tables\Contracts\HasTable;

class FinancialReport extends Page implements HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $title = 'Laporan Keuangan';
    protected static string $view = 'filament.pages.financial-report';

    public ?array $data = [];

    public ?string $startDate = null;
    public ?string $endDate = null;

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Dari Tanggal')
                ->default(Carbon::now()->startOfMonth())
                ->live(onBlur: true),
            DatePicker::make('endDate')
                ->label('Sampai Tanggal')
                ->default(Carbon::now()->endOfMonth())
                ->live(onBlur: true),
        ];
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    // Metode ini yang akan secara otomatis dipanggil oleh trait InteractsWithTable
    public function getTable(): Table
    {
        return Table::make($this)
            ->columns([
                TextColumn::make('date'),
                TextColumn::make('description'),
                TextColumn::make('category.name'),
                TextColumn::make('amount'),
            ])
            ->query($this->getTableQuery());
    }

    // Metode ini berisi logika query data untuk tabel
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Transaction::query();

        if ($this->startDate) {
            $query->whereDate('date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('date', '<=', $this->endDate);
        }

        return $query;
    }

    public function getIncomeReport(): array
    {
        return $this->getTableQuery()
            ->whereHas('category', fn($query) => $query->where('type', 'pemasukan'))
            ->groupBy('category_id')
            ->selectRaw('category_id, sum(amount) as total')
            ->with('category')
            ->get()
            ->keyBy('category.name')
            ->map(fn($item) => $item['total'])
            ->toArray();
    }

    public function getExpenseReport(): array
    {
        return $this->getTableQuery()
            ->whereHas('category', fn($query) => $query->where('type', 'pengeluaran'))
            ->groupBy('category_id')
            ->selectRaw('category_id, sum(amount) as total')
            ->with('category')
            ->get()
            ->keyBy('category.name')
            ->map(fn($item) => $item['total'])
            ->toArray();
    }

    public function getTotalIncome(): float
    {
        return $this->getTableQuery()
            ->whereHas('category', fn($query) => $query->where('type', 'pemasukan'))
            ->sum('amount');
    }

    public function getTotalExpense(): float
    {
        return $this->getTableQuery()
            ->whereHas('category', fn($query) => $query->where('type', 'pengeluaran'))
            ->sum('amount');
    }
}
