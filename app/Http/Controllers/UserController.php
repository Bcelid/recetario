<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Listar usuarios con filtro por estado, devuelve JSON.
     */
    public function index(Request $request)
    {
        $estado = $request->input('estado', '1');

        $query = User::with('role');

        if ($estado !== 'all') {
            $query->where('estado', $estado);
        }

        $users = $query->get();

        if ($request->ajax()) {
            // Retornar directamente los usuarios sin clave 'users'
            return response()->json($users);
        }

        $roles = Role::all();

        return view('users.index', compact('users', 'roles', 'estado'));
    }



    /**
     * Obtener roles activos para dropdown (JSON).
     */
    public function getRoles()
    {
        $roles = Role::where('estado', 1)->get();
        return response()->json($roles);
    }

    /**
     * Crear usuario nuevo (JSON).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|confirmed',
            'role_id'   => 'required|exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['estado'] = 1; // activo por defecto

        $user = User::create($validated);
        $user->load('role');

        return response()->json([
            'message' => 'Usuario creado exitosamente.',
            'user' => $user
        ]);
    }

    /**
     * Obtener datos de un usuario específico (JSON).
     */
    public function show(User $user)
    {
        return response()->json($user->load('role'));
    }

    /**
     * Actualizar usuario (JSON).
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username,' . $user->id,
            'phone'     => 'nullable|string|max:20',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|confirmed',
            'role_id'   => 'required|exists:roles,id',
        ]);

        // Actualizar campos excepto password
        $user->update([
            'name'      => $validated['name'],
            'lastname'  => $validated['lastname'],
            'username'  => $validated['username'],
            'phone'     => $validated['phone'] ?? null,
            'email'     => $validated['email'],
            'role_id'   => $validated['role_id'],
        ]);

        // Si vino contraseña, actualizarla hasheada
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        $user->load('role');

        return response()->json([
            'message' => 'Usuario actualizado exitosamente.',
            'user' => $user
        ]);
    }

    /**
     * Cambiar estado del usuario (activar/desactivar), responde JSON.
     */
    public function changeEstado(User $user)
    {
        $user->estado = $user->estado == 1 ? 0 : 1;
        $user->save();

        return response()->json(['message' => 'Estado actualizado']);
    }

    /**
     * Eliminar usuario permanentemente (opcional).
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json(['message' => 'Usuario eliminado permanentemente.']);
    }

    /**
     * Actualizar contraseña (JSON).
     */
    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|confirmed',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
