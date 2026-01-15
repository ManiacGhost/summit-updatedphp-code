<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    /**
     * Display a listing of all warranties.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $warranties = Warranty::all();

            return response()->json([
                'success' => true,
                'message' => 'Warranties retrieved successfully',
                'data' => $warranties,
                'count' => $warranties->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving warranties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created warranty in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'warranty_id' => 'required|string|max:50|unique:sm_warranty,warranty_id',
                'warranty_text' => 'nullable|string|max:255'
            ]);

            $warranty = Warranty::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Warranty created successfully',
                'data' => $warranty
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating warranty',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified warranty.
     *
     * @param  string  $warrantyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($warrantyId)
    {
        try {
            $warranty = Warranty::where('warranty_id', $warrantyId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Warranty retrieved successfully',
                'data' => $warranty
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving warranty',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified warranty in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $warrantyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $warrantyId)
    {
        try {
            $warranty = Warranty::where('warranty_id', $warrantyId)->firstOrFail();

            $validated = $request->validate([
                'warranty_text' => 'nullable|string|max:255'
            ]);

            $warranty->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Warranty updated successfully',
                'data' => $warranty
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating warranty',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified warranty from storage.
     *
     * @param  string  $warrantyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($warrantyId)
    {
        try {
            $warranty = Warranty::where('warranty_id', $warrantyId)->firstOrFail();
            $warranty->delete();

            return response()->json([
                'success' => true,
                'message' => 'Warranty deleted successfully',
                'data' => ['warranty_id' => $warrantyId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting warranty',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
