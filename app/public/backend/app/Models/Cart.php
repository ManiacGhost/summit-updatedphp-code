<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function total()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += ($item->price * $item->quantity);
        }
        return $total;
    }
}
