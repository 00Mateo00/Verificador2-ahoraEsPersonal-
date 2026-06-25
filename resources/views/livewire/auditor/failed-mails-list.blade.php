<div>
    <!-- Pestañas de navegación exclusivas para el Administrador -->
    @if($isAdmin)
        <div style="display: flex; gap: 8px; margin-bottom: 25px; border-bottom: 1px solid #cbd5e1; padding-bottom: 15px; flex-wrap: wrap;">
            <button type="button" 
                    wire:click="setTab('pending')" 
                    style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; cursor: pointer; border: none; transition: all 0.2s ease;
                           @if($activeTab === 'pending') background-color: #ef3340; color: #ffffff; @else background-color: #ffffff; color: #475569; border: 1px solid #cbd5e1; @endif">
                ✉️ Pendientes y Fallidos
            </button>
            <button type="button" 
                    wire:click="setTab('sent')" 
                    style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; cursor: pointer; border: none; transition: all 0.2s ease;
                           @if($activeTab === 'sent') background-color: #2b8a3e; color: #ffffff; @else background-color: #ffffff; color: #475569; border: 1px solid #cbd5e1; @endif">
                 Historial de Enviados
            </button>
        </div>
    @endif

    <!-- Barra de búsqueda e indicador de acciones masivas -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); padding: 25px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 250px;">
            <label for="searchMails" style="font-size: 0.85rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Buscar por destinatario o nombre</label>
            <input type="text" 
                   wire:model.live.debounce.350ms="search" 
                   id="searchMails" 
                   class="form-input-control-caj" 
                   placeholder="Ej: micorreo@gmail.com, Juan Pérez..." 
                   style="width: 100%;">
        </div>

        @if($activeTab === 'pending')
            <div style="display: flex; gap: 10px;">
                <button type="button" 
                        wire:click="resendAll" 
                        class="btn-dashboard-primary" 
                        wire:loading.attr="disabled"
                        wire:target="resendAll">
                    <span wire:loading.remove wire:target="resendAll"> Reintentar Todos los Pendientes</span>
                    <span wire:loading wire:target="resendAll">⏳ Reenviando...</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Alertas dinámicas internas -->
    @if (session()->has('success'))
        <x-alert type="success" title="Éxito">
            {{ session('success') }}
        </x-alert>
    @endif
    @if (session()->has('error'))
        <x-alert type="error" title="Error">
            {{ session('error') }}
        </x-alert>
    @endif
    @if (session()->has('info'))
        <x-alert type="info" title="Información">
            {{ session('info') }}
        </x-alert>
    @endif

    <!-- Tabla de datos -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span>
                @if($isAdmin)
                    {{ $activeTab === 'sent' ? 'Historial de Correos Enviados' : 'Correos Pendientes y Fallidos' }}
                @else
                    Lista de Correos que Fallaron
                @endif
            </span>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">
                @if($isAdmin)
                    {{ $activeTab === 'sent' ? 'Exitosos' : 'Pendientes de entrega' }}
                @else
                    Módulo de Auditoría
                @endif
            </span>
        </h3>

        @if($mails->isEmpty())
            <div style="text-align: center; padding: 40px; color: #94a3b8; font-size: 0.95rem;">
                📁 No se registran correos en esta sección.
            </div>
        @else
            <div style="overflow-x: auto;"> 
                <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 800px;">
                   
                    <thead>
                         
                        <tr>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Para</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Tipo de Correo</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 100px;">Intentos</th>
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 120px;">Estado</th>
                            @if($activeTab !== 'sent')
                                <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Último Error de Conexión</th>
                            @else
                                <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Fecha Envío</th>
                            @endif
                            <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: right; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Acciones</th>
                        </tr>
                        
                    </thead>
                    <tbody>
                        @foreach($mails as $mail)
                            <tr style="border-bottom: 1px solid #e2e8f0; @if($mail->status->value === 'SENT') background-color: #f0fdf4; @endif">
                                <td style="padding: 14px 16px; font-size: 0.9rem; font-weight: 600; color: #0d1b2a;">
                                    {{ $mail->user->name ?? 'Usuario de Sistema' }}
                                    @if($isAdmin && $mail->user)
                                        <span style="font-size: 0.72rem; background-color: #f1f5f9; color: #475569; padding: 2px 4px; border-radius: 4px; font-weight: normal; margin-left: 6px;">ID: #{{ $mail->user->id }}</span>
                                    @endif
                                    <span style="display: block; font-size: 0.78rem; color: #64748b; font-weight: normal; margin-top: 2px;">
                                        {{ $mail->recipient }}
                                    </span>
                                </td>
                                <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569; font-weight: 500;">
                                    {{ $mail->mail_type }}
                                </td>
                                <td style="padding: 14px 16px; text-align: center; font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    {{ $mail->attempts }}
                                </td>
                                <td style="padding: 14px 16px; text-align: center;">
                                    @if($mail->status->value === 'PENDING')
                                        <span style="background-color: rgba(245, 158, 11, 0.08); color: #d97706; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                            Pendiente
                                        </span>
                                        
                                    @elseif($mail->status->value === 'FAILED')
                                        <span style="background-color: rgba(239, 51, 64, 0.08); color: #ef3340; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                            Fallado
                                        </span>
                                    @else
                                        <span style="background-color: rgba(43, 138, 62, 0.08); color: #2b8a3e; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                            Enviado
                                        </span>
                                    @endif
                                </td>
                                @if($activeTab !== 'sent')
                                    <td style="padding: 14px 16px; font-size: 0.8rem; max-width: 320px; vertical-align: top;">
                                        @if($mail->error_message)
                                            @php $err = $mail->friendly_error; @endphp
                                            <div x-data="{ open: false, errorText: @js($mail->error_message) }">
                                                <!-- Mensaje Legible Amigable (Para todos los roles) -->
                                                <strong style="color: #ef3340; font-size: 0.85rem; display: block; margin-bottom: 2px;">
                                                    {{ $err['title'] }}
                                                </strong>
                                                <p style="color: #475569; font-size: 0.8rem; margin: 0 0 6px 0; line-height: 1.3;">
                                                    {{ $err['explanation'] }}
                                                </p>
                                                
                                                <!-- Sugerencia Operacional -->
                                                <p style="color: #2b8a3e; font-size: 0.75rem; margin: 0 0 8px 0; font-weight: 600;">
                                                    💡 {{ $err['suggestion'] }}
                                                </p>

                                                <!-- Vista colapsada/truncada del error técnico original -->
                                                <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; font-family: monospace; font-size: 0.72rem; color: #64748b; margin-bottom: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ \Illuminate\Support\Str::limit($mail->error_message, 45) }}
                                                </div>

                                                <div style="display: flex; gap: 8px; align-items: center;">
                                                    <button type="button" 
                                                            @click="open = true" 
                                                            style="background: none; border: none; color: #0F69C4; font-size: 0.72rem; font-weight: 700; cursor: pointer; padding: 0; text-decoration: underline;">
                                                        Detalles del Error 
                                                    </button>
                                                    <span style="color: #cbd5e1;">|</span>
                                                    <button type="button" 
                                                            @click="navigator.clipboard.writeText(errorText); alert('Mensaje técnico de error copiado al portapapeles')" 
                                                            style="background: none; border: none; color: #475569; font-size: 0.72rem; font-weight: 700; cursor: pointer; padding: 0; text-decoration: underline;">
                                                        📋 Copiar Error
                                                    </button>
                                                </div>

                                                <!-- Ventana Modal de Detalle de Error Adaptada -->
                                                <div x-show="open" 
                                                     x-transition 
                                                     style="position: fixed; inset: 0; background-color: rgba(13, 27, 42, 0.45); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 20px;"
                                                     x-cloak>
                                                    <div @click.away="open = false" 
                                                         style="background-color: #ffffff; border-radius: 8px; border: 1px solid #cbd5e1; width: 90vw; max-width: 90%; height: 80vh; max-height: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden;">
                                                        
                                                        <!-- Cabecera de la Modal -->
                                                        <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc; border-top-left-radius: 8px; border-top-right-radius: 8px; flex-shrink: 0;">
                                                            <strong style="color: #0d1b2a; font-size: 0.95rem;">Detalle del Error de Conexión</strong>
                                                            <button type="button" @click="open = false" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
                                                        </div>

                                                        <!-- Detalle del Error Diferenciado según el Rol del Usuario -->
                                                        <div style="padding: 20px; flex: 1; overflow-y: auto; overflow-x: hidden; background-color: #f8fafc; text-align: left;">
                                                            <!-- Sección General Comprensible -->
                                                            <div style="background-color: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 18px; margin-bottom: 15px;">
                                                                <h4 style="margin: 0 0 8px 0; color: #ef3340; font-size: 1rem;">{{ $err['title'] }}</h4>
                                                                <p style="margin: 0 0 10px 0; font-size: 0.9rem; color: #334155; line-height: 1.5;">{{ $err['explanation'] }}</p>
                                                                <strong style="display: block; font-size: 0.85rem; color: #2b8a3e;">Sugerencia de solución:</strong>
                                                                <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #2b8a3e;">💡 {{ $err['suggestion'] }}</p>
                                                            </div>

                                                            <!-- Sección de Diagnóstico Técnico Avanzado (Seguridad por Rol) -->
                                                            @if($isAdmin)
                                                                <div style="margin-top: 20px;">
                                                                    <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 6px; text-transform: uppercase;">
                                                                        Traza de Depuración Técnica (Exclusivo Administradores)
                                                                    </span>
                                                                    <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; word-break: break-all; font-family: monospace; font-size: 0.82rem; background-color: #f1f5f9; padding: 20px; border-radius: 6px; border: 1px solid #cbd5e1; color: #ef3340; width: 100%; box-sizing: border-box;" x-text="errorText"></pre>
                                                                </div>
                                                            @else
                                                                <div style="background-color: #fefbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 15px; margin-top: 15px;">
                                                                    <span style="font-size: 0.8rem; font-weight: 700; color: #b45309; display: block; margin-bottom: 4px;">Información Técnica Restringida</span>
                                                                    <p style="margin: 0; font-size: 0.8rem; color: #78350f; line-height: 1.4;">
                                                                        Como Auditor, usted visualiza la explicación simplificada recomendada. Puede copiar el bloque técnico adjunto usando el botón inferior para derivarlo con el equipo de soporte.
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Pie con Botón de Copiado -->
                                                        <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; background-color: #f8fafc; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; flex-shrink: 0;">
                                                            <button type="button" 
                                                                    @click="navigator.clipboard.writeText(errorText); alert('Mensaje de error copiado al portapapeles')" 
                                                                    class="btn-dashboard-primary">
                                                                📋 Copiar Mensaje Técnico
                                                            </button>
                                                            <button type="button" 
                                                                    @click="open = false" 
                                                                    class="btn-acc" 
                                                                    style="padding: 8px 16px; font-size: 0.8rem; border-color: #cbd5e1; border-radius: 4px;">
                                                                Cerrar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="color: #64748b; font-style: italic;">Ninguno</span>
                                        @endif
                                    </td>
                                @else
                                    <td style="padding: 14px 16px; text-align: center; font-size: 0.85rem; color: #475569; font-weight: 500;">
                                        {{ $mail->updated_at->format('d-m-Y H:i') }}
                                    </td>
                                @endif
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        @if($mail->status->value !== 'SENT')
                                            <button type="button" 
                                                    wire:click="resendIndividual({{ $mail->id }})" 
                                                    class="btn-acc" 
                                                    style="padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-color: #0F69C4; color: #0F69C4 !important; background-color: rgba(15, 105, 196, 0.02); border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;"
                                                    wire:loading.attr="disabled"
                                                    wire:target="resendIndividual({{ $mail->id }})">
                                                <span wire:loading.remove wire:target="resendIndividual({{ $mail->id }})">Reintentar ✉️</span>
                                                <span wire:loading wire:target="resendIndividual({{ $mail->id }})">Enviando... ⏳</span>
                                            </button>
                                        @endif

                                        <!-- Eliminar exclusivo para el Admin en Modo Edición -->
                                        @if($isModoEdicion)
                                            <button type="button" 
                                                    wire:click="deleteMail({{ $mail->id }})" 
                                                    wire:confirm="¿Está seguro de que desea eliminar permanentemente este registro?"
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