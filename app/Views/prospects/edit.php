<?php
// app/Views/prospects/edit.php
// Compact edit form suitable for offcanvas. Reuses same field names as create/store.
?>

<div class="prospect-edit p-2">
    <h6 class="mb-3">Editar Prospecto</h6>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger p-2 small"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?= form_open('prospects/update/' . $prospect['id']) ?>

    <div class="mb-2">
        <label for="full_name" class="form-label">Nombre Completo *</label>
        <input type="text" name="full_name" id="full_name" class="form-control form-control-sm" value="<?= esc(old('full_name', $prospect['full_name'])) ?>" required>
    </div>

    <div class="mb-2">
        <label for="email" class="form-label">Correo Electrónico</label>
        <input type="email" name="email" id="email" class="form-control form-control-sm" value="<?= esc(old('email', $prospect['email'])) ?>">
    </div>

    <div class="mb-2">
        <label for="client_type" class="form-label">Tipo de Prospecto *</label>
        <select name="client_type" id="client_type" class="form-control form-control-sm" required>
            <?php foreach ($client_types as $type): ?>
                <option value="<?= esc($type) ?>" <?= (old('client_type', $prospect['client_type'] ?? 'comprador') == $type) ? 'selected' : '' ?>><?= esc(ucfirst($type)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-2">
        <label for="phone" class="form-label">Teléfono *</label>
        <input type="text" name="phone" id="phone" class="form-control form-control-sm phone-input" value="<?= esc(old('phone', $prospect['phone'])) ?>" required autocomplete="off" maxlength="10">
    </div>

    <div class="mb-2">
        <label for="credit_amount" class="form-label">Crédito Estimado</label>
        <input type="text" name="credit_amount" id="credit_amount" class="form-control form-control-sm currency-input" value="<?= esc(old('credit_amount', $prospect['credit_amount'])) ?>">
    </div>

    <div class="mb-2">
        <label for="zone_id" class="form-label">Zona</label>
        <div class="input-group">
            <select name="zone_id" id="zone_id" class="form-control form-control-sm">
                <option value="">-- Seleccionar --</option>
                <?php foreach ($zones as $z): ?>
                    <option value="<?= esc($z['id']) ?>" <?= (old('zone_id', $prospect['zone_id']) == $z['id']) ? 'selected' : '' ?>><?= esc($z['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-success btn-sm" type="button" onclick="addNewZone(this)">+</button>
        </div>
    </div>

    <div class="mb-2">
        <label for="payment_method_id" class="form-label">Método de Pago</label>
        <select name="payment_method" id="payment_method" class="form-control form-control-sm">
            <option value="">-- Seleccionar --</option>
            <?php foreach ($payment_methods as $method): ?>
                <option value="<?= esc($method) ?>" <?= (old('payment_method', $prospect['payment_method']) == $method) ? 'selected' : '' ?>><?= esc($method) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-2">
        <label for="status" class="form-label">Estatus</label>
        <select name="status" id="status" class="form-control form-control-sm">
            <?php foreach ($statuses as $s): ?>
                <option value="<?= esc($s) ?>" <?= (old('status', $prospect['status']) == $s) ? 'selected' : '' ?>><?= esc($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (! empty($acquisitionSources)): ?>
        <?= view('prospects/partials/acquisition_fields', [
            'acquisitionSources' => $acquisitionSources,
            'selectedId' => old('acquisition_source_id', $prospect['acquisition_source_id'] ?? null),
            'detail' => old('acquisition_detail', $prospect['acquisition_detail'] ?? ''),
        ]) ?>
    <?php endif; ?>

    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-sm btn-primary me-2">Guardar cambios</button>
        <button type="button" class="btn btn-sm btn-secondary" data-offcanvas-close="1">Cancelar</button>
    </div>

    <?= form_close() ?>
</div>

<style>
    .prospect-edit .form-control-sm { font-size: .85rem; }
</style>

<script>
    // Ensure initCurrencyFormatting exists for this injected form
    (function(){
        function unformat(value){ if (!value) return ''; return String(value).replace(/[^0-9.\-]/g, '') || ''; }
        function formatNumberToDisplay(value){ if (value === null || value === undefined || value === '') return ''; var num = parseFloat(String(value).replace(/[^0-9.-]/g, '')) || 0; return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
        function attachCurrency(el){ if(!el) return; if (el.value && el.value.match(/^[0-9\.]+$/)) el.value = formatNumberToDisplay(el.value); el.addEventListener('input', function(){ var raw = el.value.replace(/[^0-9.\-]/g, ''); el.value = raw; }); el.addEventListener('blur', function(){ el.value = formatNumberToDisplay(el.value); }); var form = el.closest('form'); if (form && !form.__currency_attached){ form.addEventListener('submit', function(){ var inputs = form.querySelectorAll('.currency-input'); inputs.forEach(function(i){ i.value = unformat(i.value); }); }); form.__currency_attached = true; } }

        if (!window.initCurrencyFormatting) {
            window.initCurrencyFormatting = function(root){ root = root || document; var ci = (root.querySelectorAll) ? root.querySelectorAll('.currency-input') : []; ci.forEach(function(el){ attachCurrency(el); }); };
        }

        // Initialize for this form immediately
        document.addEventListener('DOMContentLoaded', function(){ if (window.initCurrencyFormatting) try { window.initCurrencyFormatting(document); } catch(e){} });
    })();
</script>
<script>
    (function(){
        var form = document.querySelector('.prospect-edit form');
        if (!form) return;

        function showErrors(container, errors){
            var html = '<div class="alert alert-danger"><ul>'; 
            if (typeof errors === 'object'){
                for (var k in errors){
                    if (!errors.hasOwnProperty(k)) continue;
                    var v = errors[k];
                    if (Array.isArray(v)){
                        v.forEach(function(msg){ html += '<li>'+ msg +'</li>'; });
                    } else {
                        html += '<li>'+ v +'</li>';
                    }
                }
            } else {
                html += '<li>'+ String(errors) +'</li>';
            }
            html += '</ul></div>';
            var existing = document.querySelector('.prospect-edit .form-errors');
            if (existing) existing.innerHTML = html; else {
                var div = document.createElement('div'); div.className = 'form-errors'; div.innerHTML = html; form.parentNode.insertBefore(div, form);
            }
        }

        $(form).on('submit', function(e){
            e.preventDefault();
            var $btn = $(form).find('button[type=submit]');
            $btn.prop('disabled', true).addClass('disabled');
            var data = $(form).serialize();
            $.ajax({
                url: form.action,
                method: 'POST',
                data: data,
                dataType: 'json'
            }).done(function(resp){
                if (resp && resp.success) {
                    // Update DataTable row if data provided
                    if (resp.rowId && resp.rowHtml && window.prospectsTable) {
                        try {
                            var selector = 'tr[data-id="' + resp.rowId + '"]';
                            var rowNode = $(selector);
                            var table = window.prospectsTable;
                            var row = table.row(rowNode);
                            if (row && row.node()) {
                                // Replace row cells
                                row.data(resp.rowHtml).invalidate();
                                table.draw(false);
                            }
                        } catch (err) { console.warn('Failed to update DataTable row', err); }
                    }

                    // Mostrar notificación de éxito y cerrar offcanvas
                    try { if (window.showNotice) window.showNotice('Prospecto actualizado correctamente.', 'success'); } catch(e){}
                    // Cerrar el offcanvas después de un breve delay para que el usuario vea el mensaje
                    setTimeout(function(){ try { if (window.hideProspectOffcanvas) window.hideProspectOffcanvas(); } catch(e){} }, 700);
                } else {
                    showErrors(form, resp.errors || resp.error || 'Error desconocido');
                }
            }).fail(function(jqXHR){
                var json = null;
                try { json = jqXHR.responseJSON; } catch(e){}
                if (json && json.errors) showErrors(form, json.errors); else showErrors(form, 'Error de red o servidor');
            }).always(function(){ $btn.prop('disabled', false).removeClass('disabled'); });
        });
    })();

    // Manejo de campo de teléfono - solo números, máximo 10 dígitos
    (function(){
        function attachPhoneFormatting(el) {
            if (!el) return;
            
            let isFormatting = false;
            
            el.addEventListener('input', function(e) {
                if (isFormatting) return;
                
                isFormatting = true;
                
                // Solo permitir números y limitar a 10 dígitos
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.slice(0, 10);
                }
                
                e.target.value = value;
                
                isFormatting = false;
            });
            
            // Prevenir caracteres no numéricos en keypress
            el.addEventListener('keypress', function(e) {
                // Permitir teclas de control (backspace, delete, tab, escape, enter)
                if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || 
                    e.key === 'Escape' || e.key === 'Enter' || e.key === 'ArrowLeft' || 
                    e.key === 'ArrowRight' || e.key === 'Home' || e.key === 'End') {
                    return;
                }
                
                // Solo permitir números
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
                
                // Limitar a 10 dígitos
                if (e.target.value.replace(/\D/g, '').length >= 10) {
                    e.preventDefault();
                }
            });
            
            // Limpiar en paste
            el.addEventListener('paste', function(e) {
                setTimeout(function() {
                    let value = el.value.replace(/\D/g, '');
                    if (value.length > 10) {
                        value = value.slice(0, 10);
                    }
                    el.value = value;
                }, 10);
            });
        }

        // Inicializar campos de teléfono en este formulario
        document.addEventListener('DOMContentLoaded', function(){
            var phoneInputs = document.querySelectorAll('.phone-input');
            phoneInputs.forEach(function(el){ attachPhoneFormatting(el); });
        });
    })();
</script>

<!-- Script para agregar nueva zona dinámicamente -->
<script>
function addNewZone(button) {
    Swal.fire({
        title: 'Agregar Nueva Zona',
        input: 'text',
        inputLabel: 'Nombre de la nueva zona',
        inputPlaceholder: 'Ej: Zona Centro',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: (zoneName) => {
            if (!zoneName) {
                Swal.showValidationMessage('El nombre de la zona es obligatorio');
                return false;
            }
            return fetch('<?= site_url('admin/zones/store') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: new URLSearchParams({ 'name': zoneName })
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok || !data.success) { throw new Error(data.message || 'Error del servidor'); }
                return data;
            })
            .catch(error => { Swal.showValidationMessage(`La solicitud falló: ${error.message}`); });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            const newZone = result.value.zone;
            const zoneSelects = document.querySelectorAll('select[name="zone_id"]');
            zoneSelects.forEach(select => {
                const option = new Option(newZone.name, newZone.id, true, true);
                select.add(option);
                select.dispatchEvent(new Event('change'));
            });
            Swal.fire({ title: '¡Éxito!', text: 'La nueva zona ha sido agregada y seleccionada.', icon: 'success', timer: 1500, showConfirmButton: false });
        } else if (result.isConfirmed) {
            Swal.fire({ title: 'Error', text: result.value.message || 'No se pudo guardar la zona.', icon: 'error' });
        }
    });
}
</script>
