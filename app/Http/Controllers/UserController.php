<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Mostrar lista de usuarios con filtro por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // '1' por defecto

        $users = User::with('role')
            ->when($estado !== null && $estado !== '' && $estado !== 'all', function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('id', 'desc')
            ->get();

        $roles = Role::where('estado', 1)->get();

        return view('users.index', compact('users', 'estado', 'roles'));
    }



    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        $roles = Role::where('estado', 1)->get(); // solo roles activos
        return view('users.create', compact('roles'));
    }

    /**
     * Guardar usuario nuevo.
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

        $user = User::create($validated);
        $user->load('role');
        return response()->json(['message' => 'Usuario creado', 'user' => $user]);
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit($id)
    {
        $user = User::with('role')->findOrFail($id);
        return response()->json($user);
    }


    /**
     * Actualizar usuario.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username,' . $user->id,
            'phone'     => 'nullable|string|max:20',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|confirmed',
            'role_id'   => 'required|exists:roles,id',
        ]);

        $user->update([
            'name'      => $request->name,
            'lastname'  => $request->lastname,
            'username'  => $request->username,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'role_id'   => $request->role_id,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Responder JSON si es petición AJAX
        if ($request->ajax()) {
            $user->load('role'); // para enviar el rol actualizado también
            return response()->json(['message' => 'Usuario actualizado', 'user' => $user]);
        }

        // Para peticiones normales (no AJAX), hacer redirect
        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }


    /**
     * Cambiar estado (eliminación fantasma o restaurar).
     */
    public function changeEstado(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $nuevoEstado = $user->estado ? 0 : 1;
        $user->update(['estado' => $nuevoEstado]);

        $mensaje = $nuevoEstado ? 'Usuario restaurado correctamente.' : 'Usuario desactivado correctamente.';

        // Mantener filtro actual después de cambiar estado
        $estadoFiltro = $request->get('estado', '');

        return redirect()->route('users.index', ['estado' => $estadoFiltro])->with('success', $mensaje);
    }

    /**
     * Obtener roles activos (para AJAX o select).
     */
    public function getRoles()
    {
        return response()->json(Role::where('estado', 1)->get());
    }

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
