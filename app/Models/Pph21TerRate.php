<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pph21TerRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category',
        'ptkp_status',
        'min_income',
        'max_income',
        'rate',
        'year',
        'is_active',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'rate' => 'decimal:2',
        'year' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'A' => 'Kategori A (TK/0, TK/1, K/0)',
            'B' => 'Kategori B (TK/2, TK/3, K/1, K/2)',
            'C' => 'Kategori C (K/3, K/I/0, K/I/1, K/I/2, K/I/3)',
            default => $this->category,
        };
    }

    public static function getCategoryOptions(): array
    {
        return [
            'A' => 'Kategori A (TK/0, TK/1, K/0)',
            'B' => 'Kategori B (TK/2, TK/3, K/1, K/2)',
            'C' => 'Kategori C (K/3, K/I/0, K/I/1, K/I/2, K/I/3)',
        ];
    }

    public static function getPtkpStatusByCategory(): array
    {
        return [
            'A' => ['TK/0', 'TK/1', 'K/0'],
            'B' => ['TK/2', 'TK/3', 'K/1', 'K/2'],
            'C' => ['K/3', 'K/I/0', 'K/I/1', 'K/I/2', 'K/I/3'],
        ];
    }
}
