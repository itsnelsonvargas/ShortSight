<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\DataExportService;

class UserController extends Controller
{
    public function index()
    {
        return view('user.registration');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password, // This will use the custom password setter
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export user data for GDPR compliance (Data Portability)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportData(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            $exportService = app(DataExportService::class);
            return $exportService->generateDownloadResponse($user);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Data export failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data export information without downloading (preview)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDataExportInfo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            $exportService = app(DataExportService::class);
            $data = $exportService->exportUserData($user);

            // Return summary instead of full data for preview
            return response()->json([
                'message' => 'Data export information retrieved successfully',
                'data_summary' => $data['data_summary'],
                'export_metadata' => $data['export_metadata'],
                'download_available' => true,
                'gdpr_compliant' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data export information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
