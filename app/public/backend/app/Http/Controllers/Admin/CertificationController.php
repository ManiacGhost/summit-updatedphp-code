<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    /**
     * Display a listing of all certifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $certifications = Certification::all();

            return response()->json([
                'success' => true,
                'message' => 'Certifications retrieved successfully',
                'data' => $certifications,
                'count' => $certifications->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving certifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created certification in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cert_id' => 'required|string|max:50|unique:sm_certifications,cert_id',
                'cert_text' => 'nullable|string|max:255'
            ]);

            $certification = Certification::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Certification created successfully',
                'data' => $certification
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
                'message' => 'Error creating certification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified certification.
     *
     * @param  string  $certId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($certId)
    {
        try {
            $certification = Certification::where('cert_id', $certId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Certification retrieved successfully',
                'data' => $certification
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certification not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving certification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified certification in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $certId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $certId)
    {
        try {
            $certification = Certification::where('cert_id', $certId)->firstOrFail();

            $validated = $request->validate([
                'cert_text' => 'nullable|string|max:255'
            ]);

            $certification->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Certification updated successfully',
                'data' => $certification
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certification not found'
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
                'message' => 'Error updating certification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified certification from storage.
     *
     * @param  string  $certId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($certId)
    {
        try {
            $certification = Certification::where('cert_id', $certId)->firstOrFail();
            $certification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Certification deleted successfully',
                'data' => ['cert_id' => $certId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certification not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting certification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
