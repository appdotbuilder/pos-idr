<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $sku
 * @property string|null $description
 * @property float $price
 * @property int $stock_quantity
 * @property int $low_stock_threshold
 * @property string|null $category
 * @property string|null $image_url
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleItem> $saleItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InventoryMovement> $inventoryMovements
 * @property-read bool $is_low_stock
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product active()
 * @method static \Illuminate\Database\Eloquent\Builder|Product lowStock()
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'stock_quantity',
        'low_stock_threshold',
        'category',
        'image_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'is_low_stock',
    ];

    /**
     * Get the sale items for the product.
     *
     * @return HasMany
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the inventory movements for the product.
     *
     * @return HasMany
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include products with low stock.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= low_stock_threshold');
    }

    /**
     * Get the is low stock attribute.
     *
     * @return bool
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Update stock quantity and create inventory movement.
     *
     * @param int $quantity
     * @param string $type
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $notes
     * @param int $userId
     * @return void
     */
    public function updateStock(int $quantity, string $type, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null, int $userId = 1): void
    {
        $previousStock = $this->stock_quantity;
        
        if ($type === 'stock_out') {
            $this->stock_quantity -= $quantity;
        } else {
            $this->stock_quantity += $quantity;
        }
        
        $this->save();

        $this->inventoryMovements()->create([
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $this->stock_quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'user_id' => $userId,
        ]);
    }
}