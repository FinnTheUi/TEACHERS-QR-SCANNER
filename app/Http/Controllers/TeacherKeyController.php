<?php

namespace App\Http\Controllers;

use App\Models\TeacherKey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\TeacherKeyRequest;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TeacherKeyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the teacher's keys.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $keys = $user->teacherKeys()
            ->when($request->active, function ($query) {
                return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->latest()
            ->paginate(10);

        return view('teacher-keys.index', compact('keys'));
    }

    /**
     * Store a newly created key.
     */
    public function store(TeacherKeyRequest $request): JsonResponse
    {
        try {
            $key = Auth::user()->teacherKeys()->create($request->validated());
            
            return response()->json([
                'message' => 'Key created successfully',
                'key' => $key
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create teacher key: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create key'
            ], 500);
        }
    }

    /**
     * Update the specified key.
     */
    public function update(TeacherKeyRequest $request, TeacherKey $key): JsonResponse
    {
        $this->authorize('update', $key);

        try {
            $key->update($request->validated());
            
            return response()->json([
                'message' => 'Key updated successfully',
                'key' => $key
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update teacher key: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update key'
            ], 500);
        }
    }

    /**
     * Remove the specified key.
     */
    public function destroy(TeacherKey $key): JsonResponse
    {
        $this->authorize('delete', $key);

        try {
            $key->delete();
            
            return response()->json([
                'message' => 'Key deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete teacher key: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete key'
            ], 500);
        }
    }
}
