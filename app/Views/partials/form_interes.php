<?php
/**
 * Formulario "Me interesa": crea el prospecto en el CRM.
 * @var string      $tipo    inmueble | funerario | general
 * @var int|null    $ref     id del inmueble o paquete
 * @var string|null $titulo
 * @var string|null $asunto  texto para WhatsApp (si está configurado)
 */
$tipo   = $tipo ?? 'general';
$ref    = $ref ?? null;
$wa     = sitio_whatsapp_url($asunto ?? 'Hola, me gustaría recibir información.');
$volver = '/' . ltrim(uri_string(), '/');
?>
<div class="form-interes" id="interes">
    <?php if (session('lead_ok')): ?>
        <div class="aviso-ok" role="status">
            <i class="fa-solid fa-circle-check me-1"></i> ¡Gracias! Recibimos tus datos y un asesor te contactará muy pronto.
        </div>
    <?php else: ?>
        <h3><?= esc($titulo ?? '¿Te interesa?') ?></h3>
        <p class="text-secondary small mb-3">Déjanos tus datos y un asesor te contactará para darte información, resolver dudas o agendar una visita.</p>

        <?php if (session('lead_error')): ?>
            <div class="aviso-error" role="alert"><?= esc(session('lead_error')) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('contacto') ?>" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="tipo" value="<?= esc($tipo) ?>">
            <?php if ($ref): ?><input type="hidden" name="ref" value="<?= (int) $ref ?>"><?php endif; ?>
            <input type="hidden" name="volver" value="<?= esc($volver) ?>">
            <div class="campo-trampa" aria-hidden="true"><label>No llenar<input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label></div>

            <div class="mb-3">
                <label class="form-label" for="lead_nombre">Nombre</label>
                <input class="form-control" id="lead_nombre" name="nombre" required maxlength="120" autocomplete="name" value="<?= esc(old('nombre')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="lead_tel">Teléfono / WhatsApp</label>
                <input class="form-control" id="lead_tel" name="telefono" type="tel" inputmode="tel" maxlength="20" autocomplete="tel" placeholder="10 dígitos" value="<?= esc(old('telefono')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="lead_email">Correo <span class="text-secondary fw-normal">(opcional)</span></label>
                <input class="form-control" id="lead_email" name="email" type="email" maxlength="150" autocomplete="email" value="<?= esc(old('email')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="lead_msg">Mensaje <span class="text-secondary fw-normal">(opcional)</span></label>
                <textarea class="form-control" id="lead_msg" name="mensaje" rows="3" maxlength="1000"><?= esc(old('mensaje')) ?></textarea>
            </div>
            <button class="btn btn-arena w-100" type="submit"><i class="fa-regular fa-paper-plane me-1"></i> Quiero que me contacten</button>
            <?php if ($wa): ?>
                <a class="btn btn-wa w-100 mt-2" href="<?= esc($wa) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp me-1"></i> Escribir por WhatsApp</a>
            <?php endif; ?>
            <p class="nota mb-0">Usamos tus datos solo para contactarte sobre tu solicitud. <a href="<?= base_url('politicas-de-privacidad') ?>">Aviso de privacidad</a>.</p>
        </form>
    <?php endif; ?>
</div>
