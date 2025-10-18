<?php

namespace App\Filament\Resources\NoResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;

class IncomeOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalIncome = Transaction::whereHas('category', function ($query) {
            $query->where('type', 'pemasukan');
        })->sum('amount');

        $totalExpense = Transaction::whereHas('category', function ($query) {
            $query->where('type', 'pengeluaran');
        })->sum('amount');

        $balance = $totalIncome - $totalExpense;

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalIncome, 0, ',', '.')),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalExpense, 0, ',', '.')),
            Stat::make('Saldo Saat Ini', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }
}
