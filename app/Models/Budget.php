<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'start_date',
        'end_date',
    ];

    /**
     * Get the user that owns the budget.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Pastikan relasi user juga ada
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
