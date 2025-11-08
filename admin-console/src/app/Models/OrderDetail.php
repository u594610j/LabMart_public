<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'item_name',
        'item_price',
        'item_quantity',
        'item_category',
        'paid',
        'canceled',
    ];

    /**
     * 小計計算
     */
    public function getSubtotalAttribute()
    {
        return $this->item_price * $this->item_quantity;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * キャンセル済みかどうか
     */
    public function isCanceled(): bool
    {
        return $this->canceled;
    }
}
