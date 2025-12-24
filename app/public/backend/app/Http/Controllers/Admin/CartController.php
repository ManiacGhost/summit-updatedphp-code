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
     * Get cart for authenticated user
     * GET /api/cart
     * Header: Authorization: Bearer {token}
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $response = $this->cartService->getCart($user->id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to authenticated user's cart
     * POST /api/cart/add
     * Header: Authorization: Bearer {token}
     * Body: { "product_id": "S1IF", "quantity": 1, "price": 1160.00 }
     */
    public function add(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validated = $request->validate([
                'product_id' => 'required|string',
                'quantity' => 'required|integer|min:1|max:99',
                'price' => 'required|numeric|min:0',
            ]);

            $response = $this->cartService->addItem(
                $user->id,
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
     * Update item quantity in authenticated user's cart
     * POST /api/cart/update/{itemId}
     * Header: Authorization: Bearer {token}
     * Body: { "quantity": 5 }
     */
    public function updateQuantity(Request $request, $itemId)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validated = $request->validate([
                'quantity' => 'required|integer|min:1|max:99',
            ]);

            $response = $this->cartService->updateQuantity(
                $user->id,
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
     * Remove item from authenticated user's cart
     * GET /api/cart/remove/{itemId}
     * Header: Authorization: Bearer {token}
     */
    public function remove(Request $request, $itemId)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $response = $this->cartService->removeItem($user->id, $itemId);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clear entire cart for authenticated user
     * POST /api/cart/clear
     * Header: Authorization: Bearer {token}
     */
    public function clear(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $response = $this->cartService->clearCart($user->id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
