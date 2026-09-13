<?php
/** @var array $prospect */
/** @var bool $isFuneralProspect */
/** @var array $contact_methods */
/** @var array $activity_types */
/** @var array $availableProperties */
/** @var array $propertyInterests */

$isFuneralProspect = ! empty($isFuneralProspect) || ! empty($prospect['is_funeral_prospect']);
$existingPropertyIds = array_flip(array_map(static fn ($i) => (int) $i['real_estate_id'], $propertyInterests));
$canAddProperties    = ! $isFuneralProspect && ($prospect['status'] ?? '') !== 'Cliente';
date_default_timezone_set(config('App')->appTimezone);
$defaultDateTime     = old('contact_datetime') ?: date('Y-m-d\TH:i');
$defaultNextMeeting    = date('Y-m-d\TH:i', strtotime('+1 day 10:00'));
$defaultNextMeetingEnd = date('Y-m-d\TH:i', strtotime('+1 day 10:30'));
?>
<div class="card mb-3 border-primary">
    <div class="card-header bg-primary text-white p-2">
        <h6 class="mb-0"><i class="fa fa-clipboard-list me-1"></i> Registrar seguimiento</h6>
        <small class="d-block opacity-75"><?= $isFuneralProspect
            ? 'Minuta y cita opcional — los paquetes se registran en Comprador funerario'
            : 'Minuta, interés en inmuebles y cita opcional' ?></small>
    </div>
    <div class="card-body p-2">
        <?= form_open('prospects/followup/' . $prospect['id'], ['id' => 'followUpForm']) ?>
        <div id="followUpResult"></div>

        <div class="mb-2">
            <label for="discussion_detail" class="form-label">Detalle de la conversación (minuta) *</label>
            <textarea name="discussion_detail" id="discussion_detail" class="form-control" rows="3" required placeholder="Qué platicaron, acuerdos, objeciones..."><?= old('discussion_detail') ?></textarea>
            <small class="text-muted">Máximo 500 caracteres.</small>
        </div>

        <div class="seguimiento-contact-fields mb-2">
            <div class="seguimiento-field">
                <label for="contact_method" class="form-label">Método de contacto *</label>
                <select name="contact_method" id="contact_method" class="form-control" required>
                    <?php foreach ($contact_methods as $method): ?>
                        <option value="<?= esc($method) ?>" <?= old('contact_method') == $method ? 'selected' : '' ?>><?= esc($method) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="seguimiento-field">
                <label for="contact_datetime" class="form-label">Fecha y hora *</label>
                <input type="datetime-local" name="contact_datetime" id="contact_datetime" class="form-control" value="<?= $defaultDateTime ?>" required>
                <small class="text-muted d-block mt-1">Del contacto registrado</small>
            </div>
            <?php if (! $isFuneralProspect): ?>
            <div class="seguimiento-field seguimiento-field--interest">
                <label for="interest_level" class="form-label">Nivel de interés</label>
                <select name="interest_level" id="interest_level" class="form-control">
                    <option value="Medio" selected>Medio</option>
                    <option value="Alto">Alto</option>
                    <option value="Bajo">Bajo</option>
                </select>
                <small class="text-muted d-block mt-1">Para inmuebles nuevos</small>
            </div>
            <?php endif; ?>
        </div>

        <style>
            .seguimiento-contact-fields {
                display: grid;
                grid-template-columns: 1fr;
                gap: 0.85rem 1rem;
            }
            .seguimiento-contact-fields .form-label {
                font-size: 0.875rem;
                margin-bottom: 0.35rem;
                line-height: 1.3;
            }
            .seguimiento-contact-fields .form-control {
                width: 100%;
                min-width: 0;
            }
            @media (min-width: 576px) {
                .seguimiento-contact-fields {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                .seguimiento-field--interest {
                    grid-column: 1 / -1;
                }
            }
            @media (min-width: 992px) {
                .seguimiento-contact-fields {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
                .seguimiento-field--interest {
                    grid-column: auto;
                }
            }
            /* Panel lateral (~560px): nunca apretar 3 columnas */
            #prospectOffcanvasContent .seguimiento-contact-fields {
                grid-template-columns: 1fr;
            }
            @media (min-width: 480px) {
                #prospectOffcanvasContent .seguimiento-contact-fields {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                #prospectOffcanvasContent .seguimiento-field--interest {
                    grid-column: 1 / -1;
                }
            }
        </style>

        <?php if ($canAddProperties && ! empty($availableProperties)): ?>
        <div class="border rounded p-2 mb-2 bg-light">
            <label class="form-label mb-1"><i class="fa fa-home me-1"></i> Inmuebles de interés <small class="text-muted">(marca los que le interesan)</small></label>
            <div class="row g-1" style="max-height:160px;overflow-y:auto;">
                <?php foreach ($availableProperties as $property): ?>
                    <?php if (! in_array($property['status'], ['Disponible', 'Nuevo'], true)) continue; ?>
                    <?php $pid = (int) $property['id']; ?>
                    <?php if (isset($existingPropertyIds[$pid])) continue; ?>
                    <div class="col-12 col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="real_estate_ids[]" value="<?= $pid ?>" id="re_<?= $pid ?>">
                            <label class="form-check-label small" for="re_<?= $pid ?>">
                                <?= esc($property['name']) ?> — <?= esc($property['clave']) ?>
                                <span class="text-muted">($<?= number_format($property['price'] ?? 0, 0) ?>)</span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (! $isFuneralProspect): ?>
        <div class="row mb-2">
            <div class="col-md-6">
                <label for="interest_notes" class="form-label small">Notas de interés <small class="text-muted">(opcional)</small></label>
                <input type="text" name="interest_notes" id="interest_notes" class="form-control form-control-sm" placeholder="Ej. comparó dos opciones">
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-info mb-2" style="background-color:#f8fcff;">
            <div class="card-body p-2">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="schedule_next_meeting" id="schedule_next_meeting" value="1">
                    <label class="form-check-label" for="schedule_next_meeting">
                        <i class="fa fa-calendar-plus me-1 text-info"></i> Agendar siguiente cita <small class="text-muted">(opcional)</small>
                    </label>
                </div>
                <div class="collapse mt-2" id="nextMeetingSection">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="activity_type" class="form-label small">Tipo *</label>
                            <select name="activity_type" id="activity_type" class="form-control form-control-sm">
                                <?php foreach ($activity_types as $type): ?>
                                    <option value="<?= esc($type) ?>"><?= esc($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="activity_start_time" class="form-label small">Fecha y hora *</label>
                            <input type="datetime-local" name="activity_start_time" id="activity_start_time" class="form-control form-control-sm" value="<?= $defaultNextMeeting ?>">
                        </div>
                        <div class="col-12 mb-2">
                            <label for="activity_title" class="form-label small">Título *</label>
                            <input type="text" name="activity_title" id="activity_title" class="form-control form-control-sm" value="Reunión - <?= esc($prospect['full_name']) ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="activity_end_time" class="form-label small">Hora fin</label>
                            <input type="datetime-local" name="activity_end_time" id="activity_end_time" class="form-control form-control-sm" value="<?= $defaultNextMeetingEnd ?>">
                            <small class="text-muted d-block mt-1" id="activity_end_time_hint">+30 min automático (acción rápida)</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="activity_description" class="form-label small">Notas cita</label>
                            <input type="text" name="activity_description" id="activity_description" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save me-1"></i> Guardar seguimiento</button>
        </div>
        <?= form_close() ?>
    </div>
</div>

<script>
(function() {
    const startEl = document.getElementById('activity_start_time');
    const endEl = document.getElementById('activity_end_time');
    const typeEl = document.getElementById('activity_type');
    const hintEl = document.getElementById('activity_end_time_hint');
    if (!startEl || !endEl) return;

    function formatLocalDateTime(date) {
        const pad = function(n) { return String(n).padStart(2, '0'); };
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
            + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    function isReunion() {
        return typeEl && typeEl.value === 'Reunión';
    }

    function syncActivityEndTime() {
        if (!startEl.value) return;
        const start = new Date(startEl.value);
        if (Number.isNaN(start.getTime())) return;

        const minutes = isReunion() ? 60 : 30;
        const end = new Date(start.getTime() + minutes * 60000);
        endEl.value = formatLocalDateTime(end);

        if (isReunion()) {
            endEl.readOnly = false;
            if (hintEl) hintEl.textContent = 'Reunión: ajusta la duración si hace falta (+60 min sugerido)';
        } else {
            endEl.readOnly = true;
            if (hintEl) hintEl.textContent = '+30 min automático (acción rápida)';
        }
    }

    startEl.addEventListener('change', syncActivityEndTime);
    startEl.addEventListener('input', syncActivityEndTime);
    if (typeEl) {
        typeEl.addEventListener('change', syncActivityEndTime);
    }

    syncActivityEndTime();
})();
</script>
