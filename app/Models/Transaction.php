<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Boot the model and attach event listeners.
     * Ensure that any Transaction created from application code gets a user_id
     * when possible to prevent anonymous transactions.
     */
    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->user_id) && Auth::check()) {
                $transaction->user_id = Auth::id();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     * * Kolom-kolom ini harus diizinkan untuk mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',          // Wajib untuk isolasi data
        'account_id',       // Rekening Sumber/Tujuan
        'category_id',
        'amount',
        'description',
        'date',
        'is_transfer',      // Flag untuk Transfer
        'transfer_target_id', // Rekening Pasangan Transfer
    ];

    /**
     * Get the category that owns the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the account that this transaction belongs to (the main account).
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the target account for a transfer (if applicable).
     */
    public function transferTarget(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_target_id');
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
