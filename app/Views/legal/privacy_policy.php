<?= $this->extend('template') ?>

<?= $this->section('title') ?>Políticas de Privacidad<?= $this->endSection() ?>

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
        border-bottom: 2px solid #007bff;
    }
    
    .legal-section {
        margin-bottom: 30px;
    }
    
    .legal-section h3 {
        color: #007bff;
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
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        margin: 20px 0;
    }
    
    .warning-box {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 15px;
        margin: 20px 0;
    }
    
    .youtube-required {
        background: linear-gradient(135deg, #ff0000, #cc0000);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        box-shadow: 0 4px 15px rgba(255, 0, 0, 0.2);
    }
    
    .youtube-required .btn-youtube {
        background: rgba(255, 255, 255, 0.9);
        color: #cc0000;
        border: none;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .youtube-required .btn-youtube:hover {
        background: white;
        transform: scale(1.05);
    }
    
    .contact-info {
        background-color: #e3f2fd;
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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="legal-container">
    <div class="legal-header">
        <h1><i class="fas fa-shield-alt me-3"></i>Políticas de Privacidad</h1>
        <p class="text-muted">Tony CRM - Sistema de Gestión Inmobiliaria</p>
        <p><strong>Fecha de última actualización:</strong> <?= date('d/m/Y') ?></p>
    </div>

    <div class="legal-section">
        <h3>1. Información General</h3>
        <p class="legal-text">
            En Tony CRM, nos comprometemos a proteger y respetar su privacidad. Esta Política de Privacidad explica cómo recopilamos, usamos, almacenamos y protegemos su información personal cuando utiliza nuestro sistema de gestión inmobiliaria.
        </p>
        <p class="legal-text">
            Al utilizar nuestros servicios, usted acepta las prácticas descritas en esta política. Si no está de acuerdo con estas prácticas, le recomendamos que no utilice nuestro sistema.
        </p>
    </div>

    <div class="legal-section">
        <h3>2. Información que Recopilamos</h3>
        
        <h4>2.1 Información Personal</h4>
        <p class="legal-text">Recopilamos la siguiente información personal:</p>
        <ul class="legal-list">
            <li><strong>Datos de identificación:</strong> Nombre completo, correo electrónico, teléfono</li>
            <li><strong>Información profesional:</strong> Empresa, cargo, información de contacto laboral</li>
            <li><strong>Datos de clientes:</strong> Información de prospectos y clientes para gestión inmobiliaria</li>
            <li><strong>Información financiera:</strong> Datos de crédito, límites crediticios, historial de pagos</li>
            <li><strong>Documentos:</strong> Identificaciones, comprobantes de ingresos, documentos legales</li>
        </ul>

        <h4>2.2 Información de Uso</h4>
        <ul class="legal-list">
            <li>Registros de actividad en el sistema</li>
            <li>Direcciones IP y información del dispositivo</li>
            <li>Cookies y tecnologías de seguimiento</li>
            <li>Interacciones con el sistema (clics, páginas visitadas, tiempo de sesión)</li>
        </ul>

        <h4>2.3 Integración con Servicios Externos</h4>
        <p class="legal-text">
            Nuestro sistema se integra con Google Calendar para gestión de citas. La información sincronizada está sujeta a las políticas de privacidad de Google.
        </p>

        <div class="warning-box">
            <h5><i class="fas fa-video me-2"></i>Video Explicativo Obligatorio - Permisos de Google Calendar</h5>
            <p class="mb-2">
                <strong>Requisito de Google:</strong> Debido a que nuestra aplicación solicita permisos sensibles y restringidos para acceder y modificar su Google Calendar, 
                es <strong>obligatorio</strong> que vea el siguiente video explicativo antes de otorgar permisos:
            </p>
            <div class="text-center mb-3">
                <a href="#" onclick="alert('Video en desarrollo. Por favor contacte a soporte para más información.'); return false;" class="btn btn-secondary btn-lg disabled">
                    <i class="fab fa-youtube me-2"></i>Video Explicativo en Desarrollo
                </a>
                <br><small class="text-muted mt-2 d-block">El video estará disponible próximamente</small>
            </div>
            <p class="small">
                <strong>¿Qué explica el video?</strong>
            </p>
            <ul class="small legal-list">
                <li><strong>Permisos específicos:</strong> Qué datos de su Google Calendar accedemos exactamente</li>
                <li><strong>Uso de datos:</strong> Cómo utilizamos la información de sus eventos y calendario</li>
                <li><strong>Medidas de seguridad:</strong> Protección y encriptación de sus datos de calendario</li>
                <li><strong>Límites de acceso:</strong> Qué NO hacemos con sus datos de Google Calendar</li>
                <li><strong>Control del usuario:</strong> Cómo puede revocar permisos en cualquier momento</li>
            </ul>
            <p class="small text-muted">
                <em>Este video es un requisito obligatorio de Google para aplicaciones que solicitan permisos sensibles o restringidos. 
                Sin ver este video, no podrá completar la integración con Google Calendar.</em>
            </p>
        </div>
    </div>

    <div class="legal-section">
        <h3>3. Cómo Utilizamos su Información</h3>
        <p class="legal-text">Utilizamos su información para:</p>
        <ul class="legal-list">
            <li><strong>Prestación del servicio:</strong> Gestión de prospectos, clientes y propiedades inmobiliarias</li>
            <li><strong>Comunicación:</strong> Envío de notificaciones, recordatorios y actualizaciones del sistema</li>
            <li><strong>Mejora del servicio:</strong> Análisis de uso para optimizar la funcionalidad</li>
            <li><strong>Cumplimiento legal:</strong> Cumplir con obligaciones legales y regulatorias</li>
            <li><strong>Seguridad:</strong> Proteger contra fraudes y actividades maliciosas</li>
            <li><strong>Soporte técnico:</strong> Proporcionar asistencia y resolver problemas técnicos</li>
        </ul>
    </div>

    <div class="highlight-box">
        <strong><i class="fas fa-info-circle me-2"></i>Importante:</strong> 
        Nunca vendemos, alquilamos o compartimos su información personal con terceros para fines de marketing sin su consentimiento explícito.
    </div>

    <div class="legal-section">
        <h3>4. Compartir Información</h3>
        <p class="legal-text">Podemos compartir su información solo en las siguientes circunstancias:</p>
        <ul class="legal-list">
            <li><strong>Con su consentimiento:</strong> Cuando nos proporcione autorización explícita</li>
            <li><strong>Proveedores de servicios:</strong> Terceros que nos ayudan a operar el sistema (hosting, almacenamiento)</li>
            <li><strong>Cumplimiento legal:</strong> Cuando sea requerido por ley o autoridades competentes</li>
            <li><strong>Protección de derechos:</strong> Para proteger nuestros derechos legales o los de nuestros usuarios</li>
            <li><strong>Emergencias:</strong> Para proteger la seguridad de las personas</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>5. Seguridad de los Datos</h3>
        <p class="legal-text">Implementamos múltiples medidas de seguridad para proteger su información:</p>
        <ul class="legal-list">
            <li><strong>Cifrado:</strong> Transmisión segura mediante SSL/TLS</li>
            <li><strong>Control de acceso:</strong> Autenticación de usuarios y permisos restringidos</li>
            <li><strong>Monitoreo:</strong> Supervisión continua de actividades sospechosas</li>
            <li><strong>Respaldos:</strong> Copias de seguridad regulares y recuperación de desastres</li>
            <li><strong>Capacitación:</strong> Personal entrenado en mejores prácticas de seguridad</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>6. Retención de Datos</h3>
        <p class="legal-text">
            Conservamos su información personal solo durante el tiempo necesario para cumplir con los propósitos para los cuales fue recopilada, incluyendo:
        </p>
        <ul class="legal-list">
            <li>Mientras mantenga una cuenta activa con nosotros</li>
            <li>Durante el período requerido por obligaciones legales o regulatorias</li>
            <li>Para resolver disputas y hacer cumplir nuestros acuerdos</li>
            <li>Hasta 7 años después del cierre de cuenta para registros financieros</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>7. Sus Derechos</h3>
        <p class="legal-text">Usted tiene los siguientes derechos respecto a su información personal:</p>
        <ul class="legal-list">
            <li><strong>Acceso:</strong> Solicitar copias de su información personal</li>
            <li><strong>Rectificación:</strong> Corregir información inexacta o incompleta</li>
            <li><strong>Eliminación:</strong> Solicitar la eliminación de su información (sujeto a obligaciones legales)</li>
            <li><strong>Restricción:</strong> Limitar el procesamiento de su información</li>
            <li><strong>Portabilidad:</strong> Obtener sus datos en formato estructurado</li>
            <li><strong>Oposición:</strong> Oponerse al procesamiento en ciertas circunstancias</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>8. Cookies y Tecnologías de Seguimiento</h3>
        <p class="legal-text">
            Utilizamos cookies y tecnologías similares para mejorar su experiencia de usuario:
        </p>
        <ul class="legal-list">
            <li><strong>Cookies esenciales:</strong> Necesarias para el funcionamiento del sistema</li>
            <li><strong>Cookies de rendimiento:</strong> Para analizar el uso y mejorar el sistema</li>
            <li><strong>Cookies de funcionalidad:</strong> Para recordar sus preferencias</li>
        </ul>
        <p class="legal-text">
            Puede configurar su navegador para rechazar cookies, aunque esto puede afectar la funcionalidad del sistema.
        </p>
    </div>

    <div class="legal-section">
        <h3>9. Menores de Edad</h3>
        <p class="legal-text">
            Nuestro servicio está dirigido a profesionales mayores de 18 años. No recopilamos conscientemente información personal de menores de 18 años. Si descubrimos que hemos recopilado información de un menor, la eliminaremos de inmediato.
        </p>
    </div>

    <div class="legal-section">
        <h3>10. Transferencias Internacionales</h3>
        <p class="legal-text">
            Sus datos pueden ser procesados en servidores ubicados fuera de su país de residencia. Nos aseguramos de que estas transferencias cumplan con las leyes aplicables de protección de datos y que se implementen salvaguardas adecuadas.
        </p>
    </div>

    <div class="legal-section">
        <h3>11. Cambios a esta Política</h3>
        <p class="legal-text">
            Podemos actualizar esta Política de Privacidad ocasionalmente. Le notificaremos sobre cambios significativos por correo electrónico o mediante un aviso prominente en nuestro sistema. La fecha de "última actualización" indica cuándo se realizaron los cambios más recientes.
        </p>
    </div>

    <div class="legal-section">
        <h3>12. Base Legal para el Procesamiento</h3>
        <p class="legal-text">Procesamos su información personal bajo las siguientes bases legales:</p>
        <ul class="legal-list">
            <li><strong>Consentimiento:</strong> Cuando nos ha dado autorización explícita</li>
            <li><strong>Ejecución contractual:</strong> Para cumplir con nuestros servicios</li>
            <li><strong>Interés legítimo:</strong> Para mejorar nuestros servicios y seguridad</li>
            <li><strong>Cumplimiento legal:</strong> Para cumplir con obligaciones legales</li>
        </ul>
    </div>

    <div class="contact-info">
        <h3><i class="fas fa-envelope me-2"></i>Contacto</h3>
        <p class="legal-text">
            Si tiene preguntas sobre esta Política de Privacidad o desea ejercer sus derechos, puede contactarnos:
        </p>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Correo electrónico:</strong> privacidad@tonycrm.com</p>
                <p><strong>Teléfono:</strong> +52 (55) 1234-5678</p>
            </div>
            <div class="col-md-6">
                <p><strong>Dirección:</strong><br>
                Av. Principal #123<br>
                Ciudad de México, CDMX 12345<br>
                México</p>
            </div>
        </div>
        <div class="highlight-box mt-3">
            <p><strong>Tiempo de respuesta:</strong> Nos comprometemos a responder a sus consultas dentro de 30 días hábiles.</p>
        </div>
    </div>

    <div class="text-center mt-5 pt-4 border-top">
        <p class="text-muted">
            <i class="fas fa-calendar-alt me-2"></i>
            Documento generado el <?= date('d/m/Y H:i') ?> | 
            <a href="<?= base_url() ?>" class="text-decoration-none">Volver al Sistema</a>
        </p>
    </div>
</div>

<?= $this->endSection() ?>