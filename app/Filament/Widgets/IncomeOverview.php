<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class IncomeOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Debug: Tampilkan user id yang sedang login
        Log::info('Current User ID: ' . Auth::id());
        
        // 1. Buat Kueri Dasar (Mengabaikan Transfer, Memfilter User, dan Filter Bulan Ini)
        $baseQuery = Transaction::where('user_id', Auth::id())
            ->where(function (Builder $query) {
                $query->where('is_transfer', false)
                      ->orWhereNull('is_transfer');
            })
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);

        // 2. Dapatkan total Pemasukan (amount > 0)
        $incomeQuery = (clone $baseQuery)->where('amount', '>', 0);
        
        // Hitung total pemasukan
        $totalIncome = $incomeQuery->sum('amount');
        
        $totalIncome = $incomeQuery->sum('amount');

        // 3. Dapatkan total Pengeluaran (amount < 0)
        $totalExpenseWithSign = (clone $baseQuery)
            ->where('amount', '<', 0)
            ->sum('amount');

        // 4. Ambil nilai ABSOLUT (positif) dari pengeluaran untuk ditampilkan
        $totalExpense = abs($totalExpenseWithSign);

        // 5. Perhitungan Saldo yang benar
        $balance = $totalIncome - $totalExpense;

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalIncome, 0, ',', '.')),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalExpense, 0, ',', '.')),
            Stat::make('Saldo Saat Ini', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }
}
