<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BudgetOverview extends BaseWidget
{
    // Properti statis untuk menyimpan notifikasi
    protected static array $notifications = [];

    protected function getStats(): array
    {
        // Bersihkan notifikasi lama
        self::$notifications = [];

        $budgets = Budget::where('user_id', Auth::id())
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->with('category')
            ->get();

        $stats = [];

        foreach ($budgets as $budget) {
            // Logika SPENT: Mencari transaksi pengeluaran (amount < 0) dalam rentang tanggal
            $spent = $budget->category->transactions()
                ->where('user_id', Auth::id())
                ->whereBetween('date', [$budget->start_date, $budget->end_date])
                ->where('amount', '<', 0) // Wajib: Hanya mencari nilai negatif (pengeluaran)
                ->where('is_transfer', false) // Wajib: Abaikan transaksi transfer
                ->sum('amount');

            // Ambil nilai absolut (positif) untuk ditampilkan
            $spent = abs($spent);

            $remaining = $budget->amount - $spent;
            $percentage = ($spent / $budget->amount) * 100;

            if ($percentage > 100) {
                $color = 'danger';
                // Notifikasi: Budget Terlampaui
                self::$notifications[] = [
                    'title' => 'Budget Terlampaui!',
                    'message' => 'Pengeluaran kategori ' . $budget->category->name . ' sudah melebihi budget sebesar Rp' . number_format(abs($remaining), 0, ',', '.') . '.',
                    'color' => 'danger',
                ];
            } elseif ($percentage >= 80) {
                $color = 'warning';
                // Notifikasi: Budget Hampir Habis
                self::$notifications[] = [
                    'title' => 'Budget Hampir Habis!',
                    'message' => 'Pengeluaran kategori ' . $budget->category->name . ' sudah mencapai ' . round($percentage) . '% dari budget.',
                    'color' => 'warning',
                ];
            } else {
                $color = 'success';
            }

            $stats[] = Stat::make(
                'Budget ' . $budget->category->name,
                'Rp' . number_format($remaining, 0, ',', '.')
            )
                ->description('Dihabiskan: Rp' . number_format($spent, 0, ',', '.'))
                ->color($color);
        }

        return $stats;
    }

    /**
     * Metode statis untuk mengambil notifikasi yang dihasilkan.
     */
    public static function getNotifications(): array
    {
        return self::$notifications;
    }
}
