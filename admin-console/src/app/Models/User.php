<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade',
        'card_id',
        'total_amount',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
