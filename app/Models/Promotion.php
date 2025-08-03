<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Promotion
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string $type
 * @property float $value
 * @property float|null $minimum_purchase
 * @property int|null $usage_limit
 * @property int $usage_count
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $is_valid
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion active()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion valid()
 * @method static \Database\Factories\PromotionFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class Promotion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_purchase',
        'usage_limit',
        'usage_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'is_valid',
    ];

    /**
     * Scope a query to only include active promotions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include valid promotions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereRaw('usage_count < usage_limit');
                    });
    }

    /**
     * Get the is valid attribute.
     *
     * @return bool
     */
    public function getIsValidAttribute(): bool
    {
        $now = now();
        return $this->is_active &&
               $this->start_date <= $now &&
               $this->end_date >= $now &&
               ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    /**
     * Calculate discount amount for a given subtotal.
     *
     * @param float $subtotal
     * @return float
     */
    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->is_valid || ($this->minimum_purchase && $subtotal < $this->minimum_purchase)) {
            return 0;
        }

        if ($this->type === 'percentage') {
            return $subtotal * ($this->value / 100);
        }

        return $this->value;
    }

    /**
     * Use the promotion (increment usage count).
     *
     * @return void
     */
    public function use(): void
    {
        $this->increment('usage_count');
    }
}