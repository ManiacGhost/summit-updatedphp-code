<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    /**
     * Display a listing of all series.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $series = Series::all();

            return response()->json([
                'success' => true,
                'message' => 'Series retrieved successfully',
                'data' => $series,
                'count' => $series->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving series',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created series in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'series_id' => 'required|string|max:50|unique:sm_series,series_id',
                'product_id' => 'nullable|string|max:50',
                'series_name' => 'nullable|string|max:255'
            ]);

            $series = Series::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Series created successfully',
                'data' => $series
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
                'message' => 'Error creating series',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified series.
     *
     * @param  string  $seriesId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($seriesId)
    {
        try {
            $series = Series::where('series_id', $seriesId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Series retrieved successfully',
                'data' => $series
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Series not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving series',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified series in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $seriesId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $seriesId)
    {
        try {
            $series = Series::where('series_id', $seriesId)->firstOrFail();

            $validated = $request->validate([
                'product_id' => 'nullable|string|max:50',
                'series_name' => 'nullable|string|max:255'
            ]);

            $series->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Series updated successfully',
                'data' => $series
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Series not found'
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
                'message' => 'Error updating series',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified series from storage.
     *
     * @param  string  $seriesId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($seriesId)
    {
        try {
            $series = Series::where('series_id', $seriesId)->firstOrFail();
            $series->delete();

            return response()->json([
                'success' => true,
                'message' => 'Series deleted successfully',
                'data' => ['series_id' => $seriesId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Series not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting series',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all series for a specific product.
     *
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByProduct($productId)
    {
        try {
            $series = Series::where('product_id', $productId)->get();

            return response()->json([
                'success' => true,
                'message' => 'Series retrieved successfully',
                'data' => $series,
                'count' => $series->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving series',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
