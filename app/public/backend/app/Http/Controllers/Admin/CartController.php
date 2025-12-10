<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class CartController extends Controller
{

    ////Get Cart
    public function index(Request $request)
    {
        $cart = $this->getCart($request);

        if (!$cart) {
            return response()->json(['cart' => null, 'total' => 0]);
        }

        $cart->load('items.variant.product.category', 'items.variant');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }

    ////add cart
    public function add(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart($request, true);
        $variant = ProductVariant::findOrFail($request->product_variant_id);

        $item = $cart->items()->firstOrCreate(
            ['product_variant_id' => $variant->id],
            ['price' => $variant->price, 'quantity' => 0]
        );

        $item->quantity += $request->quantity;
        $item->save();

        $cart->load('items.variant.product.category', 'items.variant.images');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }

    // Update item quantity
    public function updateQuantity(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cart = $this->getCart($request);
        if (!$cart) return response()->json(['message' => 'Cart empty'], 404);

        $item = $cart->items()->where('id', $itemId)->first();
        if (!$item) return response()->json(['message' => 'Item not found'], 404);

        $item->quantity = $request->quantity;
        $item->save();

        $cart->load('items.variant.product.category', 'items.variant');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }




    // Remove from Cart
    public function remove(Request $request, $itemId)
    {
        $cart = $this->getCart($request);
        if (!$cart) return response()->json(['message' => 'Cart empty'], 404);

        $cart->items()->where('id', $itemId)->delete();
        // return response()->json(['message' => 'Item removed']);

        //  $cart = $this->getCart($request);

        // if (!$cart) {
        //     return response()->json(['cart' => null, 'total' => 0]);
        // }

        $cart->load('items.variant.product.category', 'items.variant');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }
    // Remove all items from Cart
    public function clear(Request $request)
    {
        $cart = $this->getCart($request);
        if (!$cart) return response()->json(["status" => "empty", 'message' => 'Cart empty'], 404);

        $cart->items()->delete();
        return response()->json(["status" => "clear", 'message' => 'Item removed']);
    }

    // Helper: get or create cart
    private function getCart(Request $request, $create = false)
    {
        $userId = $request->user()?->id;
        $sessionId = $request->session()->getId();

        $cart = Cart::where('user_id', $userId)
            ->orWhere('session_id', $sessionId)
            ->first();

        if (!$cart && $create) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
        }

        return $cart;
    }
}
