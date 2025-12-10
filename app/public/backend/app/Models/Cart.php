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
        return $this->hasMany(CartItem::class)->with('variant');
    }

    public function total()
    {
        return $this->items->sum(fn ($item) => $item->price * $item->quantity);
    }
}
