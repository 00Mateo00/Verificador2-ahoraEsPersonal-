<!-- Modal de Advertencia de Expiración -->
<div id="caj-session-warning-modal" style="display:none; position: fixed; inset: 0; background-color: rgba(13, 27, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 99999; padding: 20px;">
    <div style="background-color: #ffffff; border-radius: 12px; border: 1px solid #cbd5e1; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); padding: 30px; text-align: left;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
            <span style="font-size: 2rem;">⚠️</span>
            <strong style="color: #9f1239; font-size: 1.25rem; font-weight: 700;">¿Sigue ahí? Su sesión va a expirar</strong>
        </div>
        <p style="color: #475569; font-size: 0.92rem; line-height: 1.6; margin: 0 0 25px 0;">
            Por motivos de seguridad y de acuerdo a las políticas de la intranet, su sesión de acceso caducará pronto debido a inactividad detectada. ¿Desea mantener su sesión activa?
        </p>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button id="caj-session-logout-btn" type="button" class="btn-acc" style="padding: 10px 18px; font-size: 0.85rem; border-color: #cbd5e1; border-radius: 6px; cursor: pointer; background: #f8fafc; color: #475569;">
                Cerrar sesión ahora
            </button>
            <button id="caj-session-extend-btn" type="button" class="btn-primary-caj" style="padding: 10px 20px; font-size: 0.85rem; border-radius: 6px; cursor: pointer; height: auto;">
                Sí, seguir activo
            </button>
        </div>
    </div>
</div>

<!-- Modal de Sesión Expirada -->
<div id="caj-session-expired-modal" style="display:none; position: fixed; inset: 0; background-color: rgba(13, 27, 42, 0.7); backdrop-filter: blur(5px); align-items: center; justify-content: center; z-index: 99999; padding: 20px;">
    <div style="background-color: #ffffff; border-radius: 12px; border: 1px solid #cbd5e1; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); padding: 35px; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 15px;">🔒</div>
        <strong style="color: #ef3340; font-size: 1.30rem; font-weight: 700; display: block; margin-bottom: 10px;">Su sesión ha caducado</strong>
        <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0 0 25px 0;">
            Su sesión de acceso a la Intranet CAJBIOBIO ha expirado debido a inactividad prolongada. Por favor, vuelva a ingresar sus credenciales para continuar.
        </p>
        <button id="caj-session-relogin-btn" type="button" class="btn-primary-caj" style="padding: 12px 30px; font-size: 0.9rem; border-radius: 6px; cursor: pointer; width: 100%;">
            Ir al Inicio de Sesión
        </button>
    </div>
</div>