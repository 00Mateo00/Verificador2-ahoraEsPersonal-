<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Muestra el catálogo general de unidades, regiones y operadores.
     */
    public function index()
    {
        $search = request('search');
        $usuarios = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('rol', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        $regiones = Region::all();

        return view('admin.edicion', compact('usuarios', 'search', 'regiones'));
    }

    /**
     * Creación de Región (Incluye creación automática de Director Regional).
     */
    public function crearRegion(Request $request)
    {
        if (! session('modo_edicion')) {
            abort(403, 'Acción bloqueada. Debe activar el Modo Edición.');
        }

        $request->validate([
            'region_nombre' => 'required|string|max:50',
            'director_nombre' => 'required|string|max:255',
            'director_email' => 'required|email|unique:users,email',
            'region_id' => 'integer|unique:region,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->director_nombre,
                'email' => $request->director_email,
                'password' => Hash::make('password'),
                'rol' => 'director',
                'activo' => true,
                'password_changed_at' => null,
            ]);

            Region::create([
                'id' => $request->region_id,
                'region_nombre' => $request->region_nombre,
                'user_id' => $user->id,
            ]);
        });

        return back()->with('success', "La Región '{$request->region_nombre}' y su Director Regional han sido creados con éxito.");
    }

    /**
     * Creación de Unidad Operativa (Incluye creación automática de Operador de Unidad).
     */
    public function crearUnidad(Request $request)
    {
        if (! session('modo_edicion')) {
            abort(403, 'Acción bloqueada. Debe activar el Modo Edición.');
        }

        $request->validate([
            'unidad_nombre' => 'required|string|max:255',
            'unidad_email' => 'required|email|unique:users,email',
            'region_id' => 'required|exists:region,id',
            'unidad_id' => 'nullable|integer|unique:unidad,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->unidad_nombre,
                'email' => $request->unidad_email,
                'password' => Hash::make('password'),
                'rol' => 'unidad',
                'activo' => true,
                'password_changed_at' => null,
            ]);

            Unidad::create([
                'id' => $request->unidad_id ?: null,
                'region_id' => $request->region_id,
                'user_id' => $user->id,
            ]);
        });

        return back()->with('success', "La Unidad '{$request->unidad_nombre}' y su Operador han sido creados con éxito.");
    }

    /**
     * Creación de Usuario de Sistema (Admin, Auditor o Cargador).
     */
    public function crearUsuario(Request $request)
    {
        if (! session('modo_edicion')) {
            abort(403, 'Acción bloqueada. Debe activar el Modo Edición.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:admin,auditor,cargador',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password'),
            'rol' => $request->rol,
            'activo' => true,
            'password_changed_at' => null,
        ]);

        return back()->with('success', "Usuario '{$request->name}' con rol '{$request->rol}' creado con éxito.");
    }

    /**
     * Activa el modo edición crítica en sesión.
     */
    public function entrarEdicion()
    {
        session([
            'modo_edicion' => true,
            'modo_edicion_last_activity' => time(),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Modo edición activado. Las opciones de edición crítica ahora son usables.');
    }

    /**
     * Desactiva el modo edición interactiva en sesión.
     */
    public function salirEdicion()
    {
        session()->forget([
            'modo_edicion',
            'modo_edicion_last_activity',
            'auth.password_confirmed_at',
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Modo edición desactivado. Ha retornado al modo de visualización segura.');
    }

    /**
     * Modifica el estado de activación de un operador del sistema.
     */
    public function toggleUsuario(User $user)
    {
        if (! session('modo_edicion')) {
            abort(403, 'Acción bloqueada. Debe activar el Modo Edición para realizar modificaciones en las unidades.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puede deshabilitar su propia cuenta de administrador.');
        }

        $user->update([
            'activo' => ! $user->activo,
        ]);

        $statusText = $user->activo ? 'habilitada' : 'deshabilitada';

        return back()->with('success', "La cuenta de {$user->name} ha sido {$statusText} con éxito.");
    }
}
