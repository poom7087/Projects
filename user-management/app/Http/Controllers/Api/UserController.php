<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     * แสดงรายชื่อผู้ใช้งานทั้งหมด
     */
    public function index()
    {
        $users = User::select(
            'id',
            'name',
            'email',
            'created_at',
            'updated_at'
        )
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ], 200);
    }

    /**
     * POST /api/users
     * เพิ่มผู้ใช้งานใหม่
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }

    /**
     * GET /api/users/{user}
     * แสดงข้อมูลผู้ใช้งานรายบุคคล
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 200);
    }

    /**
     * PUT/PATCH /api/users/{user}
     * แก้ไขข้อมูลผู้ใช้งาน
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'updated_at' => $user->updated_at,
            ],
        ], 200);
    }

    /**
     * DELETE /api/users/{user}
     * ลบผู้ใช้งาน
     */
    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ((int) $request->user()->id === (int) $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account here',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }
}
