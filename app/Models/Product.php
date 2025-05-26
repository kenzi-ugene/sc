<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer'
    ];

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function getPriceForUser($user = null)
    {
        if (!$user) {
            return $this->price;
        }

        $basePrice = $this->price;

        if ($user->role === 'member') {
            $basePrice = $basePrice * 0.9;
        }

        return $basePrice;
    }
}
