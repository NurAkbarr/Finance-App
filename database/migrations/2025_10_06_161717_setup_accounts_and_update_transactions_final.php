<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // 1. BUAT TABEL ACCOUNTS DULU
    Schema::create('accounts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->decimal('initial_balance', 15, 2)->default(0);
        $table->timestamps();
    });

    // 2. MODIFIKASI TABEL TRANSACTIONS
    Schema::table('transactions', function (Blueprint $table) {
        // Tambahkan foreign key ke Account
        $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade')->after('category_id');
        // Tambahkan flag untuk transfer
        $table->boolean('is_transfer')->default(false)->after('amount');
        // Tambahkan kolom pasangan transfer
        $table->unsignedBigInteger('transfer_target_id')->nullable()->after('is_transfer');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    // 1. UNDO MODIFIKASI TRANSACTIONS
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropForeign(['account_id']);
        $table->dropColumn(['account_id', 'is_transfer', 'transfer_target_id']);
    });

    // 2. HAPUS TABEL ACCOUNTS
    Schema::dropIfExists('accounts');
}
};
