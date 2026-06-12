<?php

namespace App\Livewire\Actividades;

use App\Models\Actividad;
use App\Models\Unidad;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class VerificarPendientes extends Component
{
    use WithPagination;

    // Escuchar el evento del componente hijo de forma segura para refrescar la lista paginada
    #[On('actividad-verificada')]
    public function refreshList()
    {
        // funcion vacia, solo existe para forzar un re-render;
    }

    public function render()
    {
        // Recuperar la unidad asociada al usuario autenticado usando query() explícito
        $unidad = Unidad::query()->where('user_id', Auth::id())->first();
        $unidadId = $unidad ? $unidad->id : null;

        // Mostrar solo las actividades cargadas por el excel que pertenezcan a la unidad asignada
        $actividades = $unidadId
            ? Actividad::query()->where('estado', 'CARGADA')
                ->where('unidad_id_asignada', $unidadId)
                ->orderBy('FECHA', 'desc')
                ->paginate(10)
            : collect(); // Retornar colección vacía si el usuario no tiene una unidad operativa asignada

        return view('livewire.actividades.verificar-pendientes', [
            'actividades' => $actividades,
        ]);
    }
}
