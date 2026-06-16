<div>
    <!-- Barra de búsqueda e indicador de acciones masivas -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); padding: 25px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 250px;">
            <label for="searchMails" style="font-size: 0.85rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Buscar por destinatario o asunto</label>
            <input type="text" 
                   wire:model.live.debounce.350ms="search" 
                   id="searchMails" 
                   class="form-input-control-caj" 
                   placeholder="Ej: micorreo@gmail.com, Aviso: Nuevas actividades..." 
                   style="width: 100%;">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" 
                    wire:click="resendAll" 
                    class="btn-primary-caj" 
                    style="padding: 12px 24px; font-size: 0.9rem; background-color: #2b8a3e; border: none; display: inline-flex; align-items: center; gap: 8px;"
                    wire:loading.attr="disabled"
                    wire:target="resendAll">
                <span wire:loading.remove wire:target="resendAll">🔄 Reintentar Todos los Pendientes</span>
                <span wire:loading wire:target="resendAll">⏳ Reenviando...</span>
            </button>
        </div>
    </div>

    <!-- Alertas dinámicas internas del componente Livewire -->
    @if (session()->has('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-size: 0.9rem; font-weight: 600;">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-size: 0.9rem; font-weight: 600;">
            ⚠️ {{ session('error') }}
        </div>
    @endif
    @if (session()->has('info'))
        <div style="background-color: #e2f0fd; color: #0b4e91; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #bfdbfe; font-size: 0.9rem; font-weight: 600;">
            ℹ️ {{ session('info') }}
        </div>
    @endif

    <!-- Tabla del catálogo -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span>Lista de Correos que Fallaron</span>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Operación Síncrona</span>
        </h3>

        @if($mails->isEmpty())
            <div style="text-align: center; padding: 40px; color: #94a3b8; font-size: 0.95rem;">
                📁 No se registran correos fallidos o pendientes de entrega.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Destinatario</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Asunto</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 100px;">Intentos</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 120px;">Estado</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Último Error de Conexión</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: right; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mails as $mail)
                            <tr style="border-bottom: 1px solid #e2e8f0; @if($mail->status === 'SENT') background-color: #f0fdf4; @endif">
                                <td style="padding: 14px 16px; font-size: 0.9rem; font-weight: 600; color: #0d1b2a;">
                                    {{ $mail->recipient }}
                                    <span style="display: block; font-size: 0.75rem; color: #94a3b8; font-weight: normal; font-family: monospace;">
                                        {{ class_basename($mail->mailable_class) }}
                                    </span>
                                </td>
                                <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569;">
                                    {{ $mail->subject }}
                                </td>
                                <td style="padding: 14px 16px; text-align: center; font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    {{ $mail->attempts }}
                                </td>
                                <td style="padding: 14px 16px; text-align: center;">
                                    @if($mail->status === 'SENT')
                                        <span style="background-color: rgba(43, 138, 62, 0.08); color: #2b8a3e; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                            Enviado
                                        </span>
                                    @elseif($mail->status === 'FAILED')
                                        <span style="background-color: rgba(239, 51, 64, 0.08); color: #ef3340; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                            Fallado
                                        </span>
                                    @else
                                        <span style="background-color: rgba(245, 158, 11, 0.08); color: #d97706; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 14px 16px; font-size: 0.8rem; color: #ef3340; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $mail->error_message }}">
                                    {{ $mail->error_message ?: 'Ninguno' }}
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        @if($mail->status !== 'SENT')
                                            <button type="button" 
                                                    wire:click="resendIndividual({{ $mail->id }})" 
                                                    class="btn-acc" 
                                                    style="padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-color: #0F69C4; color: #0F69C4 !important; background-color: rgba(15, 105, 196, 0.02); border-radius: 4px;"
                                                    wire:loading.attr="disabled">
                                                Reintentar ✉️
                                            </button>
                                        @endif

                                        <!-- Eliminar exclusivo para el Admin en Modo Edición -->
                                        @if($isModoEdicion)
                                            <button type="button" 
                                                    wire:click="deleteMail({{ $mail->id }})" 
                                                    wire:confirm="¿Está seguro de que desea eliminar permanentemente este registro de correo fallido?"
                                                    class="btn-acc" 
                                                    style="padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-color: #ef3340; color: #ef3340 !important; background-color: rgba(239, 51, 64, 0.02); border-radius: 4px;">
                                                Eliminar 🗑️
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div style="margin-top: 25px;">
                {{ $mails->links() }}
            </div>
        @endif
    </div>
</div>