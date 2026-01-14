<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
class AdminController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();

        return response()->json($users);
    }

    public function updateRoles(Request $request, $userId)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name'
        ]);

        $user = User::findOrFail($userId);

        // Nájdeme role podľa mena
        $roleIds = Role::whereIn('name', $request->roles)->pluck('id');

        // Aktualizujeme role
        $user->roles()->sync($roleIds);

        return response()->json([
            'message' => 'Role používateľa boli úspešne aktualizované.',
            'user' => $user->load('roles')
        ]);
    }

}
