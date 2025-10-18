<?php

namespace App\Filament\Resources\NoResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Carbon\Carbon;

class IncomeExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Keuangan Bulanan';
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $transactions = Transaction::orderBy('date')->get()->groupBy(function ($item) {
            return Carbon::parse($item->date)->format('M Y');
        });

        $months = $transactions->keys();
        $incomes = $transactions->map(function ($monthTransactions) {
            return $monthTransactions->filter(function ($transaction) {
                return $transaction->category->type === 'pemasukan';
            })->sum('amount');
        });

        $expenses = $transactions->map(function ($monthTransactions) {
            return $monthTransactions->filter(function ($transaction) {
                return $transaction->category->type === 'pengeluaran';
            })->sum('amount');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomes->values(),
                    'borderColor' => '#4CAF50',
                    'backgroundColor' => '#E8F5E9',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenses->values(),
                    'borderColor' => '#F44336',
                    'backgroundColor' => '#FFEBEE',
                ],
            ],
            'labels' => $months,
        ];
    }
}
