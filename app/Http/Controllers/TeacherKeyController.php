<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TeacherKey;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\TeacherKeyRequest;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class TeacherKeyController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

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
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        $keys = TeacherKey::query()
            ->where('user_id', $user->id)
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
            /** @var User $user */
            $user = Auth::user();
            
            $validatedData = $request->validated();
            $validatedData['user_id'] = $user->id;
            
            $key = TeacherKey::create($validatedData);
            
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
     * Display the specified key.
     *
     * @param  \App\Models\TeacherKey  $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TeacherKey $key): JsonResponse
    {
        $this->authorize('view', $key);
        
        return response()->json($key);
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
