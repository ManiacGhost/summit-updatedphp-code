<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Exception;

class CartService
{
    private const MAX_QUANTITY = 99;
    private const MIN_QUANTITY = 1;

    /**
     * Get or create cart for user
     */
    private function findOrCreateCart(int $userId, bool $create = false): ?Cart
    {
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart && $create) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => null
            ]);
        }

        return $cart;
    }

    /**
     * Get cart with all items
     */
    public function getCart(int $userId): array
    {
        $cart = $this->findOrCreateCart($userId);

        if (!$cart) {
            return [
                'success' => true,
                'data' => [
                    'cart' => null,
                    'items' => [],
                    'total' => 0,
                    'item_count' => 0
                ]
            ];
        }

        $items = $cart->items()->get();

        $total = 0;
        foreach ($items as $item) {
            $total += ($item->quantity * $item->price);
        }

        return [
            'success' => true,
            'data' => [
                'cart' => [
                    'id' => $cart->id,
                    'user_id' => $cart->user_id,
                    'created_at' => $cart->created_at,
                    'updated_at' => $cart->updated_at
                ],
                'items' => $items,
                'total' => round($total, 2),
                'item_count' => $items->count()
            ]
        ];
    }

    /**
     * Add item to cart
     */
    public function addItem(int $userId, string $productId, int $quantity, float $price): array
    {
        if ($quantity < self::MIN_QUANTITY || $quantity > self::MAX_QUANTITY) {
            throw new Exception("Quantity must be between " . self::MIN_QUANTITY . " and " . self::MAX_QUANTITY);
        }

        $cart = $this->findOrCreateCart($userId, true);

        $item = $cart->items()->firstOrCreate(
            ['product_id' => $productId],
            ['price' => $price, 'quantity' => 0]
        );

        $item->quantity += $quantity;
        
        if ($item->quantity > self::MAX_QUANTITY) {
            $item->quantity = self::MAX_QUANTITY;
        }

        $item->save();

        return $this->getCartForResponse($userId);
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $userId, int $itemId, int $quantity): array
    {
        if ($quantity < self::MIN_QUANTITY || $quantity > self::MAX_QUANTITY) {
            throw new Exception("Quantity must be between " . self::MIN_QUANTITY . " and " . self::MAX_QUANTITY);
        }

        $cart = $this->findOrCreateCart($userId);
        if (!$cart) {
            throw new Exception("Cart not found for user");
        }

        // Ensure the item belongs to this user's cart
        $item = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();
        
        if (!$item) {
            throw new Exception("Item not found in your cart");
        }
        
        $item->quantity = $quantity;
        $item->save();

        return $this->getCartForResponse($userId);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $userId, int $itemId): array
    {
        $cart = $this->findOrCreateCart($userId);
        if (!$cart) {
            throw new Exception("Cart not found");
        }

        $cart->items()->where('id', $itemId)->delete();

        return $this->getCartForResponse($userId);
    }

    /**
     * Clear entire cart
     */
    public function clearCart(int $userId): array
    {
        $cart = $this->findOrCreateCart($userId);
        if (!$cart) {
            throw new Exception("Cart not found");
        }

        $cart->items()->delete();

        return [
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => [
                'cart' => [
                    'id' => $cart->id,
                    'user_id' => $cart->user_id,
                ],
                'items' => [],
                'total' => 0,
                'item_count' => 0
            ]
        ];
    }

    /**
     * Get cart total
     */
    public function getCartTotal(int $userId): float
    {
        $cart = $this->findOrCreateCart($userId);
        if (!$cart) {
            return 0;
        }

        $total = 0;
        foreach ($cart->items as $item) {
            $total += ($item->quantity * $item->price);
        }

        return round($total, 2);
    }

    /**
     * Get item count in cart
     */
    public function getCartItemCount(int $userId): int
    {
        $cart = $this->findOrCreateCart($userId);
        if (!$cart) {
            return 0;
        }

        return $cart->items()->count();
    }

    /**
     * Check if item exists in cart
     */
    public function itemExists(int $userId, int $itemId): bool
    {
        $cart = $this->findOrCreateCart($userId);
        if (!$cart) {
            return false;
        }

        return $cart->items()->where('id', $itemId)->exists();
    }

    /**
     * Get formatted cart for API response
     */
    private function getCartForResponse(int $userId): array
    {
        $cart = $this->findOrCreateCart($userId);

        if (!$cart) {
            return [
                'success' => true,
                'data' => [
                    'cart' => null,
                    'items' => [],
                    'total' => 0,
                    'item_count' => 0
                ]
            ];
        }

        $items = $cart->items()->get();

        $total = 0;
        foreach ($items as $item) {
            $total += ($item->quantity * $item->price);
        }

        return [
            'success' => true,
            'data' => [
                'cart' => [
                    'id' => $cart->id,
                    'user_id' => $cart->user_id,
                    'created_at' => $cart->created_at,
                    'updated_at' => $cart->updated_at
                ],
                'items' => $items,
                'total' => round($total, 2),
                'item_count' => $items->count()
            ]
        ];
    }
}
