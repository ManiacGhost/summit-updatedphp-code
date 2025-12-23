<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CartService;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart by user_id
     * GET /api/cart?user_id=1
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->query('user_id') ?? $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id is required'
                ], 400);
            }

            $response = $this->cartService->getCart($userId);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to cart
     * POST /api/cart/add
     */
    public function add(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'product_id' => 'required|string',
                'quantity' => 'required|integer|min:1|max:99',
                'price' => 'required|numeric|min:0',
            ]);

            $response = $this->cartService->addItem(
                $validated['user_id'],
                $validated['product_id'],
                $validated['quantity'],
                $validated['price']
            );

            return response()->json($response, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update item quantity
     * POST /api/cart/update/{itemId}
     */
    public function updateQuantity(Request $request, $itemId)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'quantity' => 'required|integer|min:1|max:99',
            ]);

            $response = $this->cartService->updateQuantity(
                $validated['user_id'],
                $itemId,
                $validated['quantity']
            );

            return response()->json($response);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove item from cart
     * GET /api/cart/remove/{itemId}?user_id=1
     */
    public function remove(Request $request, $itemId)
    {
        try {
            $userId = $request->query('user_id') ?? $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id is required'
                ], 400);
            }

            $response = $this->cartService->removeItem($userId, $itemId);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clear entire cart
     * POST /api/cart/clear
     */
    public function clear(Request $request)
    {
        try {
            $userId = $request->input('user_id') ?? $request->query('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id is required'
                ], 400);
            }

            $response = $this->cartService->clearCart($userId);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
