<div>
    @if($actividades->isEmpty())
        <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 40px; text-align: center; color: #64748b;">
            <span style="font-size: 2rem;">🎉</span>
            <p style="margin: 10px 0 0; font-weight: 600; font-size: 1.1rem; color: #2b8a3e;">¡Excelente! No tienes actividades pendientes de verificación.</p>
            <p style="margin: 5px 0 0; font-size: 0.9rem;">Todas las actividades asignadas a tu unidad ya cuentan con su respectivo respaldo.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @foreach($actividades as $act)
                <livewire:actividades.verificar-actividad-card :act="$act" :key="'verificar-card-'.$act->actividad_id" />
            @endforeach
        </div>

        <div style="margin-top: 20px;">
            {{ $actividades->links() }}
        </div>
    @endif
</div>