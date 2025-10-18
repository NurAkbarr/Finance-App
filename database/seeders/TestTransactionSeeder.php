<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat account dulu jika belum ada
        $account = \App\Models\Account::firstOrCreate(
            ['user_id' => 1],
            [
                'name' => 'Cash',
                'description' => 'Default Cash Account',
                'balance' => 0
            ]
        );

        // Tambahkan transaksi dengan account_id
        \App\Models\Transaction::create([
            'user_id' => 1,
            'account_id' => $account->id,
            'amount' => 1000000,
            'description' => 'Test Income',
            'date' => now(),
            'is_transfer' => false,
            'category_id' => 1,
        ]);

        \App\Models\Transaction::create([
            'user_id' => 1,
            'account_id' => $account->id,
            'amount' => -500000,
            'description' => 'Test Expense',
            'date' => now(),
            'is_transfer' => false,
            'category_id' => 1,
        ]);
    }
}
