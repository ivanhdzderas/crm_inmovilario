<?= $this->extend('template') ?>

<?= $this->section('title') ?>Términos y Condiciones<?= $this->endSection() ?>

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
        border-bottom: 2px solid #28a745;
    }
    
    .legal-section {
        margin-bottom: 30px;
    }
    
    .legal-section h3 {
        color: #28a745;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .legal-text {
        text-align: justify;
        margin-bottom: 15px;
    }
    
    .highlight-box {
        background-color: #f8f9fa;
        border-left: 4px solid #28a745;
        padding: 15px;
        margin: 20px 0;
    }
    
    .contact-info {
        background-color: #e8f5e8;
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
        <h1><i class="fas fa-file-contract me-3"></i>Términos y Condiciones</h1>
        <p class="text-muted">Tony CRM - Sistema de Gestión Inmobiliaria</p>
        <p><strong>Fecha de última actualización:</strong> <?= date('d/m/Y') ?></p>
    </div>

    <div class="legal-section">
        <h3>1. Aceptación de los Términos</h3>
        <p class="legal-text">
            Al acceder y utilizar Tony CRM, usted acepta estar sujeto a estos Términos y Condiciones de Uso. Si no está de acuerdo con alguno de estos términos, no debe utilizar nuestro servicio.
        </p>
    </div>

    <div class="legal-section">
        <h3>2. Descripción del Servicio</h3>
        <p class="legal-text">
            Tony CRM es un sistema de gestión inmobiliaria que permite a los usuarios gestionar prospectos, clientes, propiedades, documentos, agenda y actividades relacionadas con el sector inmobiliario.
        </p>
    </div>

    <div class="legal-section">
        <h3>3. Registro y Cuenta de Usuario</h3>
        <ul class="legal-list">
            <li>Debe proporcionar información precisa y actualizada durante el registro</li>
            <li>Es responsable de mantener la confidencialidad de sus credenciales</li>
            <li>Solo está autorizado para tener una cuenta</li>
            <li>Debe ser mayor de 18 años para usar el servicio</li>
            <li>Se compromete a notificar inmediatamente cualquier uso no autorizado de su cuenta</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>4. Uso Aceptable</h3>
        <p class="legal-text">Al usar Tony CRM, se compromete a:</p>
        <ul class="legal-list">
            <li>Utilizar el servicio únicamente para fines legales y comerciales legítimos</li>
            <li>No interferir con el funcionamiento del sistema</li>
            <li>No intentar acceder a cuentas de otros usuarios</li>
            <li>No transmitir virus, malware o código malicioso</li>
            <li>Cumplir con todas las leyes aplicables</li>
            <li>Respectar la propiedad intelectual de terceros</li>
        </ul>
    </div>

    <div class="highlight-box">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Importante:</strong> 
        El uso indebido del sistema puede resultar en la suspensión o terminación inmediata de su cuenta.
    </div>

    <div class="legal-section">
        <h3>5. Propiedad Intelectual</h3>
        <p class="legal-text">
            Tony CRM y todo su contenido, incluyendo pero no limitado a software, textos, imágenes, logos, y funcionalidades, son propiedad exclusiva de la empresa y están protegidos por leyes de propiedad intelectual.
        </p>
    </div>

    <div class="legal-section">
        <h3>6. Privacidad y Protección de Datos</h3>
        <p class="legal-text">
            Su privacidad es importante para nosotros. El uso de información personal está regido por nuestra 
            <a href="<?= base_url('politicas-de-privacidad') ?>" target="_blank">Política de Privacidad</a>, 
            la cual forma parte integral de estos términos.
        </p>
    </div>

    <div class="legal-section">
        <h3>7. Limitación de Responsabilidad</h3>
        <p class="legal-text">
            En la máxima medida permitida por la ley:
        </p>
        <ul class="legal-list">
            <li>El servicio se proporciona "tal como está" sin garantías de ningún tipo</li>
            <li>No garantizamos que el servicio esté libre de errores o interrupciones</li>
            <li>No somos responsables por pérdidas directas, indirectas, incidentales o consecuenciales</li>
            <li>Su uso del servicio es bajo su propio riesgo</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>8. Suspensión y Terminación</h3>
        <p class="legal-text">
            Nos reservamos el derecho de suspender o terminar su acceso al servicio en caso de:
        </p>
        <ul class="legal-list">
            <li>Violación de estos términos y condiciones</li>
            <li>Uso fraudulento o ilegal del servicio</li>
            <li>Falta de pago (si aplica)</li>
            <li>Solicitud del usuario</li>
        </ul>
    </div>

    <div class="legal-section">
        <h3>9. Modificaciones</h3>
        <p class="legal-text">
            Nos reservamos el derecho de modificar estos términos en cualquier momento. Los cambios serán efectivos inmediatamente después de su publicación en el sistema. El uso continuado del servicio constituye aceptación de los términos modificados.
        </p>
    </div>

    <div class="legal-section">
        <h3>10. Ley Aplicable</h3>
        <p class="legal-text">
            Estos términos se rigen por las leyes de México. Cualquier disputa será resuelta en los tribunales competentes de la Ciudad de México.
        </p>
    </div>

    <div class="contact-info">
        <h3><i class="fas fa-envelope me-2"></i>Contacto</h3>
        <p class="legal-text">
            Para preguntas sobre estos Términos y Condiciones, contáctenos en:
        </p>
        <p><strong>Correo:</strong> legal@tonycrm.com | <strong>Teléfono:</strong> +52 (55) 1234-5678</p>
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