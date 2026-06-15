<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DescargaVerificadorController extends Controller
{
    /**
     * Descarga de forma segura un archivo verificador privado tras validar autenticación.
     */
    public function descargar(Archivo $archivo)
    {
        // Defensa de Entrada: Comprobar autenticación de sesión
        if (!Auth::check()) {
            abort(403, 'Acceso denegado. Debe iniciar sesión para descargar documentos.');
        }

        // Validar política de visualización horizontal sobre la actividad propietaria del archivo
        $actividad = $archivo->actividad;
        if (!$actividad || !Auth::user()->can('view', $actividad)) {
            abort(403, 'No tiene permisos de acceso regional o de unidad para descargar los respaldos de esta actividad.');
        }

        // Verificar la existencia física real del archivo en el almacenamiento local seguro
        if (!Storage::disk('local')->exists($archivo->archivo_ruta)) {
            abort(404, 'El archivo solicitado no existe o fue removido del servidor.');
        }

        // Retornar descarga directa segura usando Storage
        return Storage::disk('local')->download(
            $archivo->archivo_ruta,
            $archivo->archivo_nombre
        );
    }
}