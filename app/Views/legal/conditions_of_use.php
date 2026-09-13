<?= $this->extend('template') ?>

<?= $this->section('title') ?>Condiciones de Uso<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .legal-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        line-height: 1.6;
    }
    
    .legal-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #fd7e14;
    }
    
    .legal-section {
        margin-bottom: 30px;
    }
    
    .legal-section h3 {
        color: #fd7e14;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .legal-section h4 {
        color: #6c757d;
        margin-top: 20px;
        margin-bottom: 10px;
    }
    
    .legal-text {
        text-align: justify;
        margin-bottom: 15px;
    }
    
    .highlight-box {
        background-color: #fff3cd;
        border-left: 4px solid #fd7e14;
        padding: 15px;
        margin: 20px 0;
    }
    
    .warning-box {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 15px;
        margin: 20px 0;
    }
    
    .contact-info {
        background-color: #fff3cd;
        padding: 20px;
        border-radius: 8px;
        margin-top: 30px;
    }
    
    ul.legal-list {
        padding-left: 20px;
    }
    
    ul.legal-list li {
        margin-bottom: 8px;
    }
    
    .usage-example {
        background-color: #e7f3ff;
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid #0d6efd;
        margin: 15px 0;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="legal-container">
    <div class="legal-header">
        <h1><i class="fas fa-tasks me-3"></i>Condiciones de Uso</h1>
        <p class="text-muted">Tony CRM - Sistema de Gestión Inmobiliaria</p>
        <p><strong>Fecha de última actualización:</strong> <?= date('d/m/Y') ?></p>
    </div>

    <div class="legal-section">
        <h3>1. Propósito y Alcance</h3>
        <p class="legal-text">
            Estas Condiciones de Uso establecen las reglas específicas para la utilización de Tony CRM, complementando los Términos y Condiciones generales. Definen cómo debe utilizarse el sistema, qué está permitido y qué está prohibido durante su uso diario.
        </p>
    </div>

    <div class="legal-section">
        <h3>2. Acceso y Autenticación</h3>
        
        <h4>2.1 Credenciales de Acceso</h4>
        <ul class="legal-list">
            <li><strong>Responsabilidad personal:</strong> Sus credenciales de acceso son personales e intransferibles</li>
            <li><strong>Seguridad de contraseña:</strong> Debe utilizar contraseñas seguras y cambiarlas periódicamente</li>
            <li><strong>Cierre de sesión:</strong> Debe cerrar sesión al finalizar su trabajo, especialmente en computadoras compartidas</li>
            <li><strong>Notificación de vulneración:</strong> Debe reportar inmediatamente cualquier acceso no autorizado</li>
        </ul>

        <h4>2.2 Horarios de Disponibilidad</h4>
        <p class="legal-text">
            El sistema está disponible 24/7, sin embargo, puede haber mantenimientos programados que se notificarán con al menos 24 horas de anticipación.
        </p>
    </div>

    <div class="legal-section">
        <h3>3. Gestión de Datos</h3>
        
        <h4>3.1 Información de Clientes</h4>
        <ul class="legal-list">
            <li><strong>Veracidad:</strong> Toda la información ingresada debe ser veraz y actualizada</li>
            <li><strong>Confidencialidad:</strong> Los datos de clientes son confidenciales y no deben compartirse fuera del sistema</li>
            <li><strong>Autorización:</strong> Solo debe ingresar datos de personas que han dado su consentimiento</li>
            <li><strong>Actualización:</strong> Debe mantener actualizada la información de contacto y estado de clientes</li>
        </ul>

        <h4>3.2 Documentos y Archivos</h4>
        <ul class="legal-list">
            <li><strong>Formato permitido:</strong> Solo archivos en formatos estándar (PDF, DOC, XLS, JPG, PNG)</li>
            <li><strong>Tamaño máximo:</strong> Archivos no mayores a 10MB por documento</li>
            <li><strong>Contenido apropiado:</strong> No subir contenido ilegal, ofensivo o no relacionado con el trabajo</li>
            <li><strong>Respaldo:</strong> Se recomienda mantener copias de seguridad de documentos importantes</li>
        </ul>
    </div>

    <div class="usage-example">
        <h5><i class="fas fa-lightbulb me-2"></i>Ejemplo de Uso Correcto</h5>
        <p class="mb-0">Al crear un nuevo prospecto, complete todos los campos requeridos con información real del cliente, adjunte documentos relevantes como identificación y comprobantes de ingresos, y programe una cita de seguimiento en la agenda.</p>
    </div>

    <div class="legal-section">
        <h3>4. Funcionalidades del Sistema</h3>
        
        <h4>4.1 Gestión de Prospectos</h4>
        <ul class="legal-list">
            <li><strong>Seguimiento oportuno:</strong> Debe dar seguimiento a los prospectos en los tiempos establecidos</li>
            <li><strong>Actualización de estatus:</strong> Mantener actualizado el estado de cada prospecto</li>
            <li><strong>Registro de actividades:</strong> Documentar todas las interacciones con clientes</li>
            <li><strong>Asignación correcta:</strong> Solo trabajar con prospectos asignados a usted</li>
        </ul>

        <h4>4.2 Agenda y Calendario</h4>
        <ul class="legal-list">
            <li><strong>Puntualidad:</strong> Respetar las citas programadas en la agenda</li>
            <li><strong>Actualización inmediata:</strong> Reportar cambios de citas tan pronto como ocurran</li>
            <li><strong>Sincronización Google:</strong> Si usa la integración con Google Calendar, mantener ambos calendarios sincronizados</li>
            <li><strong>Motivos de reprogramación:</strong> Siempre proporcionar razón válida al mover citas</li>
        </ul>

        <h4>4.3 Documentación y Minutas</h4>
        <ul class="legal-list">
            <li><strong>Registro completo:</strong> Documentar todas las interacciones importantes con clientes</li>
            <li><strong>Claridad:</strong> Escribir minutas claras y comprensibles</li>
            <li><strong>Oportunidad:</strong> Registrar actividades el mismo día que ocurrieron</li>
            <li><strong>Seguimiento:</strong> Programar actividades de seguimiento cuando sea necesario</li>
        </ul>
    </div>

    <div class="highlight-box">
        <strong><i class="fas fa-info-circle me-2"></i>Recordatorio:</strong>
        El uso eficiente del sistema mejora la productividad y la calidad del servicio a clientes. Utilice todas las funcionalidades disponibles para optimizar su trabajo.
    </div>

    <div class="legal-section">
        <h3>5. Uso Prohibido</h3>
        
        <p class="legal-text">Está estrictamente prohibido:</p>
        <ul class="legal-list">
            <li><strong>Compartir accesos:</strong> Permitir que otras personas usen su cuenta</li>
            <li><strong>Modificar el sistema:</strong> Intentar alterar, hackear o dañar el sistema</li>
            <li><strong>Uso personal:</strong> Utilizar el sistema para asuntos no relacionados con el trabajo</li>
            <li><strong>Exportación no autorizada:</strong> Extraer datos masivos sin autorización</li>
            <li><strong>Spam o promoción:</strong> Usar el sistema para enviar correos no solicitados</li>
            <li><strong>Competencia desleal:</strong> Usar información del sistema para beneficio de competidores</li>
            <li><strong>Violación de privacidad:</strong> Acceder a información de clientes no asignados</li>
        </ul>
    </div>

    <div class="warning-box">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Advertencia:</strong>
        El incumplimiento de estas condiciones puede resultar en suspensión inmediata de la cuenta y posibles acciones legales.
    </div>

    <div class="legal-section">
        <h3>6. Rendimiento y Productividad</h3>
        
        <h4>6.1 Métricas de Uso</h4>
        <ul class="legal-list">
            <li><strong>Actividad mínima:</strong> Debe mantener actividad regular en el sistema</li>
            <li><strong>Seguimiento de prospectos:</strong> Al menos una interacción por semana con prospectos activos</li>
            <li><strong>Actualización de estatus:</strong> Cambios de estado deben reflejar la realidad del proceso de venta</li>
            <li><strong>Uso de agenda:</strong> Programar y completar actividades de manera consistente</li>
        </ul>

        <h4>6.2 Calidad de Datos</h4>
        <ul class="legal-list">
            <li><strong>Información completa:</strong> Llenar todos los campos requeridos</li>
            <li><strong>Datos actualizados:</strong> Mantener información de contacto actualizada</li>
            <li><strong>Documentación suficiente:</strong> Subir documentos necesarios para cada prospecto</li>
            <li><strong>Seguimiento documentado:</strong> Registrar todas las interacciones importantes</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>7. Integración con Google Calendar</h3>
        
        <h4>7.1 Autorización</h4>
        <p class="legal-text">
            Al conectar su Google Calendar personal, autoriza la sincronización bidireccional de eventos relacionados con el trabajo. Esto significa que:
        </p>
        <ul class="legal-list">
            <li>Los eventos creados en Tony CRM aparecerán en su Google Calendar</li>
            <li>Los cambios en Google Calendar pueden sincronizarse de vuelta al sistema</li>
            <li>Solo eventos laborales deben sincronizarse</li>
        </ul>

        <h4>7.2 Responsabilidades</h4>
        <ul class="legal-list">
            <li><strong>Separación de calendarios:</strong> Mantenga separados eventos personales y laborales</li>
            <li><strong>Sincronización responsable:</strong> Verifique que las sincronizaciones sean correctas</li>
            <li><strong>Privacidad:</strong> No sincronice información confidencial de clientes fuera del sistema autorizado</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>8. Soporte y Reportes</h3>
        
        <h4>8.1 Solicitud de Soporte</h4>
        <ul class="legal-list">
            <li><strong>Canales oficiales:</strong> Use solo los canales de soporte autorizados</li>
            <li><strong>Información completa:</strong> Proporcione detalles específicos del problema</li>
            <li><strong>Tiempo de respuesta:</strong> Permita hasta 24 horas para respuesta inicial</li>
            <li><strong>Seguimiento:</strong> Confirme la resolución de problemas reportados</li>
        </ul>

        <h4>8.2 Reporte de Errores</h4>
        <ul class="legal-list">
            <li><strong>Inmediato:</strong> Reporte errores del sistema tan pronto como los detecte</li>
            <li><strong>Detallado:</strong> Describa pasos para reproducir el error</li>
            <li><strong>Evidencia:</strong> Incluya capturas de pantalla cuando sea posible</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>9. Actualizaciones y Mantenimiento</h3>
        
        <p class="legal-text">
            El sistema se actualiza regularmente para mejorar funcionalidades y seguridad:
        </p>
        <ul class="legal-list">
            <li><strong>Mantenimiento programado:</strong> Se notificará con anticipación</li>
            <li><strong>Actualizaciones automáticas:</strong> Algunas mejoras se aplicarán automáticamente</li>
            <li><strong>Nuevas funciones:</strong> Se proporcionará capacitación cuando sea necesario</li>
            <li><strong>Compatibilidad:</strong> Mantenga su navegador actualizado</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>10. Monitoreo y Auditoría</h3>
        
        <h4>10.1 Registros de Actividad</h4>
        <p class="legal-text">
            El sistema registra automáticamente todas las actividades para propósitos de:
        </p>
        <ul class="legal-list">
            <li>Seguridad y prevención de fraudes</li>
            <li>Mejora de procesos y productividad</li>
            <li>Cumplimiento legal y regulatorio</li>
            <li>Soporte técnico y resolución de problemas</li>
        </ul>

        <h4>10.2 Auditorías</h4>
        <ul class="legal-list">
            <li><strong>Revisiones periódicas:</strong> El uso del sistema puede ser auditado</li>
            <li><strong>Cooperación:</strong> Debe cooperar con auditorías internas</li>
            <li><strong>Transparencia:</strong> Mantenga registros claros y precisos</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>11. Cumplimiento y Consecuencias</h3>
        
        <h4>11.1 Incumplimiento</h4>
        <p class="legal-text">El incumplimiento de estas condiciones puede resultar en:</p>
        <ul class="legal-list">
            <li><strong>Advertencia verbal o escrita</strong></li>
            <li><strong>Suspensión temporal</strong> del acceso al sistema</li>
            <li><strong>Revocación permanente</strong> de privilegios</li>
            <li><strong>Acciones disciplinarias</strong> según políticas de la empresa</li>
            <li><strong>Acciones legales</strong> en casos graves</li>
        </ul>

        <h4>11.2 Proceso de Apelación</h4>
        <p class="legal-text">
            Si considera que una acción disciplinaria es injusta, puede apelar contactando al departamento de Recursos Humanos dentro de 5 días hábiles.
        </p>
    </div>

    <div class="contact-info">
        <h3><i class="fas fa-headset me-2"></i>Soporte y Contacto</h3>
        <div class="row">
            <div class="col-md-6">
                <h5>Soporte Técnico</h5>
                <p><strong>Email:</strong> soporte@tonycrm.com</p>
                <p><strong>Teléfono:</strong> +52 (55) 1234-5678 ext. 100</p>
                <p><strong>Horario:</strong> Lunes a Viernes, 9:00 - 18:00</p>
            </div>
            <div class="col-md-6">
                <h5>Consultas Legales</h5>
                <p><strong>Email:</strong> legal@tonycrm.com</p>
                <p><strong>Teléfono:</strong> +52 (55) 1234-5678 ext. 200</p>
                <p><strong>Horario:</strong> Lunes a Viernes, 10:00 - 16:00</p>
            </div>
        </div>
        <div class="highlight-box mt-3">
            <p><strong>Capacitación:</strong> Para solicitar capacitación adicional sobre el uso del sistema, contacte a capacitacion@tonycrm.com</p>
        </div>
    </div>

    <div class="text-center mt-5 pt-4 border-top">
        <p class="text-muted">
            <i class="fas fa-calendar-alt me-2"></i>
            Documento generado el <?= date('d/m/Y H:i') ?> | 
            <a href="<?= base_url() ?>" class="text-decoration-none">Volver al Sistema</a> |
            <a href="<?= base_url('terminos-y-condiciones') ?>" class="text-decoration-none">Términos y Condiciones</a> |
            <a href="<?= base_url('politicas-de-privacidad') ?>" class="text-decoration-none">Políticas de Privacidad</a>
        </p>
    </div>
</div>

<?= $this->endSection() ?>