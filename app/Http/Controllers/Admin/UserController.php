<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // El middleware ya verificó admin:leer,modules
        $adminUser = Auth::guard('admin')->user();
        $currentUser = $adminUser->user;

        // Cargar Administradores con sus relaciones
        $admins = Admin::with(['user'])
            ->orderBy('id')
            ->get();

        // Cargar Staff con sus relaciones
        $staffs = Staff::with(['user'])
            ->orderBy('id')
            ->get();

        return view('admin.users.index', compact('admins', 'staffs', 'currentUser', 'adminUser'));
    }
    public function searchByDocument(Request $request)
    {
        $request->validate([
            'tipo_documento' => 'required|string|in:DNI,RUC,CE,Pasaporte',
            'nro_documento' => 'required|string|max:20'
        ]);

        $user = User::where('tipo_documento', $request->tipo_documento)
                    ->where('nro_documento', $request->nro_documento)
                    ->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'user' => [
                    'username' => $user->username,
                    'nombres' => $user->nombres,
                    'apellido_paterno' => $user->apellido_paterno,
                    'apellido_materno' => $user->apellido_materno,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
    }

    public function create()
    {
        $adminUser = Auth::guard('admin')->user();
        $currentUser = $adminUser->user;

        // Tipos de documento disponibles
        $tiposDocumento = [
            'DNI' => 'DNI',
            'RUC' => 'RUC',
            'CE' => 'Carné de Extranjería',
            'Pasaporte' => 'Pasaporte'
        ];

        // Niveles para admin
        $nivelesAdmin = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'soporte' => 'Soporte'
        ];

        // Opciones para staff
        $cargosStaff = [
            'Vendedor' => 'Vendedor',
            'Supervisor' => 'Supervisor',
            'Gerente' => 'Gerente',
            'Asistente' => 'Asistente',
            'Cajero' => 'Cajero',
            'Almacenero' => 'Almacenero'
        ];

        $areasStaff = [
            'Ventas' => 'Ventas',
            'Administración' => 'Administración',
            'Logística' => 'Logística',
            'Atención al Cliente' => 'Atención al Cliente',
            'Almacén' => 'Almacén',
            'Marketing' => 'Marketing'
        ];

        return view('admin.users.create', compact(
            'currentUser',
            'adminUser',
            'tiposDocumento',
            'nivelesAdmin',
            'cargosStaff',
            'areasStaff'
        ));
    }

    public function store(Request $request)
    {
        $adminUser = Auth::guard('admin')->user();
        $currentUser = $adminUser->user;

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'tipo_documento' => 'required|string|in:DNI,RUC,CE,Pasaporte',
            'nro_documento' => 'required|string|max:20|unique:users',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|string|in:admin,staff,client',
        ]);

        if ($request->user_type === 'admin') {
            $request->validate([
                'nivel_admin' => 'required|string|in:super_admin,admin,soporte',
                'is_active_admin' => 'sometimes|boolean',
            ]);
        }

        if ($request->user_type === 'staff') {
            $request->validate([
                'cargo_staff' => 'required|string|max:255',
                'area_staff' => 'required|string|max:255',
                'fecha_contratacion' => 'required|date',
                'salario_staff' => 'nullable|numeric|min:0',
                'is_active_staff' => 'sometimes|boolean',
            ]);
        }

        if ($request->user_type === 'client') {
            $request->validate([
                'is_active_client' => 'sometimes|boolean',
            ]);
        }

        $user = User::create([
            'username' => $validated['username'],
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? '',
            'email' => $validated['email'],
            'tipo_documento' => $validated['tipo_documento'],
            'nro_documento' => $validated['nro_documento'],
            'telefono' => $validated['telefono'],
            'password' => Hash::make($validated['password']),
        ]);

        switch ($request->user_type) {
            case 'admin':
                Admin::create([
                    'user_id' => $user->id,
                    'nivel' => $request->nivel_admin,
                    'is_active' => $request->boolean('is_active_admin', true),
                ]);
                break;

            case 'staff':
                Staff::create([
                    'user_id' => $user->id,
                    'cargo' => $request->cargo_staff,
                    'area' => $request->area_staff,
                    'fecha_contratacion' => $request->fecha_contratacion,
                    'salario' => $request->salario_staff,
                    'is_active' => $request->boolean('is_active_staff', true),
                ]);
                break;

            case 'client':
                Client::create([
                    'user_id' => $user->id,
                    'is_active' => $request->boolean('is_active_client', true),
                ]);
                break;
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $adminUser = Auth::guard('admin')->user();
        $currentUser = $adminUser->user;

        // Cargar las relaciones del usuario
        $user->load(['admin', 'staff', 'client']);

        // Determinar TODOS los tipos de usuario que tiene
        $userTypes = [];
        if ($user->admin) {
            $userTypes[] = 'admin';
        }
        if ($user->staff) {
            $userTypes[] = 'staff';
        }
        if ($user->client) {
            $userTypes[] = 'client';
        }

        // Tipos de documento disponibles
        $tiposDocumento = [
            'DNI' => 'DNI',
            'RUC' => 'RUC',
            'CE' => 'Carné de Extranjería',
            'Pasaporte' => 'Pasaporte'
        ];

        // Niveles para admin
        $nivelesAdmin = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'soporte' => 'Soporte'
        ];

        // Opciones para staff
        $cargosStaff = [
            'Vendedor' => 'Vendedor',
            'Supervisor' => 'Supervisor',
            'Gerente' => 'Gerente',
            'Asistente' => 'Asistente',
            'Cajero' => 'Cajero',
            'Almacenero' => 'Almacenero'
        ];

        $areasStaff = [
            'Ventas' => 'Ventas',
            'Administración' => 'Administración',
            'Logística' => 'Logística',
            'Atención al Cliente' => 'Atención al Cliente',
            'Almacén' => 'Almacén',
            'Marketing' => 'Marketing'
        ];

        return view('admin.users.edit', compact(
            'user',
            'userTypes', // Cambiamos de userType a userTypes (array)
            'currentUser',
            'adminUser',
            'tiposDocumento',
            'nivelesAdmin',
            'cargosStaff',
            'areasStaff'
        ));
    }

    public function update(Request $request, User $user)
    {
        $adminUser = Auth::guard('admin')->user();
        $currentUser = $adminUser->user;

        // Obtener los roles existentes del usuario
        $existingUserTypes = [];
        if ($user->admin) {
            $existingUserTypes[] = 'admin';
        }
        if ($user->staff) {
            $existingUserTypes[] = 'staff';
        }
        if ($user->client) {
            $existingUserTypes[] = 'client';
        }

        // Validación básica del usuario
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'tipo_documento' => 'required|string|in:DNI,RUC,CE,Pasaporte',
            'nro_documento' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            // Nota: Ya no validamos user_type como requerido porque puede venir de existing_user_types
        ]);

        // Validar que al menos hay un rol existente
        if (empty($existingUserTypes)) {
            return back()->withErrors(['error' => 'El usuario debe tener al menos un rol asignado.']);
        }

        // Validaciones para cada rol existente
        if (in_array('admin', $existingUserTypes)) {
            $request->validate([
                'nivel_admin' => 'required|string|in:super_admin,admin,soporte',
                'is_active_admin' => 'sometimes|boolean',
            ]);
        }

        if (in_array('staff', $existingUserTypes)) {
            $request->validate([
                'cargo_staff' => 'required|string|max:255',
                'area_staff' => 'required|string|max:255',
                'fecha_contratacion' => 'required|date',
                'salario_staff' => 'nullable|numeric|min:0',
                'is_active_staff' => 'sometimes|boolean',
            ]);
        }

        if (in_array('client', $existingUserTypes)) {
            $request->validate([
                'is_active_client' => 'sometimes|boolean',
            ]);
        }

        // Actualizar datos del usuario
        $userData = [
            'username' => $validated['username'],
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? '',
            'email' => $validated['email'],
            'tipo_documento' => $validated['tipo_documento'],
            'nro_documento' => $validated['nro_documento'],
            'telefono' => $validated['telefono'],
        ];

        // Actualizar contraseña solo si se proporciona
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Actualizar los perfiles según los roles existentes
        $this->syncUserRoles($user, $request, $existingUserTypes);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    // Remove the specified resource from storage.
    public function destroy(User $user)
    {
        // El middleware ya verificó admin:eliminar,user
        $currentUser = Auth::guard('admin')->user();

        // No permitir eliminarse a sí mismo
        if ($currentUser->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminarte a ti mismo'
            ], 403);
        }

        // No permitir eliminar super admins si no eres super admin
        if ($user->admin && $user->admin->isSuperAdmin() && !$currentUser->user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar un Super Admin'
            ], 403);
        }

        // Eliminar primero los roles relacionados (soft delete)
        if ($user->admin) {
            $user->admin->delete(); // Soft delete en admins
        }

        if ($user->staff) {
            $user->staff->delete(); // Soft delete en staff
        }


        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario y todos sus roles eliminados correctamente'
            ]);
        }

        return redirect()->route('admin.users.index') // Nota: cambié a 'users.index' (plural)
            ->with('success', 'Usuario y todos sus roles eliminados correctamente.');
    }

    // Sincronizar el rol del usuario
    private function syncUserRoles(User $user, Request $request, array $existingUserTypes)
    {
        // Actualizar rol Admin si existe
        if (in_array('admin', $existingUserTypes)) {
            if ($user->admin) {
                $user->admin->update([
                    'nivel' => $request->nivel_admin,
                    'is_active' => $request->boolean('is_active_admin', true),
                ]);
            } else {
                // Si por alguna razón no existe pero debería, lo creamos
                Admin::create([
                    'user_id' => $user->id,
                    'nivel' => $request->nivel_admin,
                    'is_active' => $request->boolean('is_active_admin', true),
                ]);
            }
        }

        // Actualizar rol Staff si existe
        if (in_array('staff', $existingUserTypes)) {
            if ($user->staff) {
                $user->staff->update([
                    'cargo' => $request->cargo_staff,
                    'area' => $request->area_staff,
                    'fecha_contratacion' => $request->fecha_contratacion,
                    'salario' => $request->salario_staff,
                    'is_active' => $request->boolean('is_active_staff', true),
                ]);
            } else {
                // Si por alguna razón no existe pero debería, lo creamos
                Staff::create([
                    'user_id' => $user->id,
                    'cargo' => $request->cargo_staff,
                    'area' => $request->area_staff,
                    'fecha_contratacion' => $request->fecha_contratacion,
                    'salario' => $request->salario_staff,
                    'is_active' => $request->boolean('is_active_staff', true),
                ]);
            }
        }

        // Actualizar rol Cliente si existe
        if (in_array('client', $existingUserTypes)) {
            if ($user->client) {
                $user->client->update([
                    'is_active' => $request->boolean('is_active_client', true),
                ]);
            } else {
                // Si por alguna razón no existe pero debería, lo creamos
                Client::create([
                    'user_id' => $user->id,
                    'is_active' => $request->boolean('is_active_client', true),
                ]);
            }
        }
    }
}
