<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of all materials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $materials = Material::all();

            return response()->json([
                'success' => true,
                'message' => 'Materials retrieved successfully',
                'data' => $materials,
                'count' => $materials->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving materials',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created material in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'material_id' => 'required|string|max:50|unique:sm_materials,material_id',
                'material_name' => 'nullable|string|max:255'
            ]);

            $material = Material::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Material created successfully',
                'data' => $material
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
                'message' => 'Error creating material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified material.
     *
     * @param  string  $materialId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($materialId)
    {
        try {
            $material = Material::where('material_id', $materialId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Material retrieved successfully',
                'data' => $material
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified material in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $materialId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $materialId)
    {
        try {
            $material = Material::where('material_id', $materialId)->firstOrFail();

            $validated = $request->validate([
                'material_name' => 'nullable|string|max:255'
            ]);

            $material->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Material updated successfully',
                'data' => $material
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found'
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
                'message' => 'Error updating material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified material from storage.
     *
     * @param  string  $materialId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($materialId)
    {
        try {
            $material = Material::where('material_id', $materialId)->firstOrFail();
            $material->delete();

            return response()->json([
                'success' => true,
                'message' => 'Material deleted successfully',
                'data' => ['material_id' => $materialId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting material',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
