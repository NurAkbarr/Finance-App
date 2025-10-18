<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BasePage;
use App\Filament\Widgets\IncomeOverview;      // Import widget Saldo/Pemasukan/Pengeluaran
use App\Filament\Widgets\IncomeExpenseChart; // Import widget Grafik
use App\Filament\Widgets\BudgetOverview;     // Import widget Budget

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        // Pastikan urutan dan nama kelas widget sudah benar
        return [
            IncomeOverview::class,
            IncomeExpenseChart::class,
            BudgetOverview::class,
        ];
    }
}
