<!-- app/Views/prospects/create.php (compact form for offcanvas) -->
<?php
helper(['form']);
$funerarioMode = ! empty($funerarioMode);
?>

<div class="prospect-form">
    <div class="header d-flex justify-content-between align-items-center mb-2">
        <h4 class="m-0 text-primary"><i class="fa fa-<?= $funerarioMode ? 'monument' : 'user-plus' ?> me-1"></i> <?= $funerarioMode ? 'Nuevo comprador funerario' : 'Nuevo Prospecto' ?></h4>
        <a href="<?= site_url($funerarioMode ? 'prospects?interest=funerario' : 'prospects') ?>" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger small mb-2" role="alert">
            <?php 
                $errors = session()->getFlashdata('error');
                if (is_array($errors)):
                    echo "<ul class='mb-0 pl-3'>";
                    foreach ($errors as $error):
                        echo "<li>" . esc($error) . "</li>";
                    endforeach;
                    echo "</ul>";
                else:
                    echo "<div>" . esc($errors) . "</div>";
                endif;
            ?>
        </div>
    <?php endif; ?>

    <?= form_open('prospects/store', ['class' => 'compact-form']) ?>
        <?php if ($funerarioMode): ?>
            <input type="hidden" name="prospect_context" value="funerario">
            <input type="hidden" name="client_type" value="comprador">
            <input type="hidden" name="status" value="Nuevo">
        <?php endif; ?>

        <div class="form-group mb-2">
            <label for="full_name" class="form-label small">Nombre Completo *</label>
            <input type="text" name="full_name" id="full_name" class="form-control form-control-sm" value="<?= old('full_name') ?>" placeholder="Ej: Iván Hernández" required>
        </div>

        <?php if (! $funerarioMode): ?>
        <div class="form-group mb-2">
            <label for="client_type" class="form-label small">Tipo de Prospecto *</label>
            <select name="client_type" id="client_type" class="form-control form-control-sm" required>
                <?php foreach ($client_types as $type): ?>
                    <?php $selectedType = old('client_type') ? (old('client_type') == $type ? 'selected' : '') : ($type == 'comprador' ? 'selected' : ''); ?>
                    <option value="<?= esc($type) ?>" <?= $selectedType ?>><?= esc(ucfirst($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="d-flex gap-2 mb-2">
            <div class="flex-fill">
                <label for="email" class="form-label small">Correo</label>
                <input type="email" name="email" id="email" class="form-control form-control-sm" value="<?= old('email') ?>" placeholder="ejemplo@correo.com">
            </div>
            <div style="width:120px;">
                <label for="phone" class="form-label small">Teléfono *</label>
                <input type="text" name="phone" id="phone" class="form-control form-control-sm phone-input" value="<?= old('phone') ?>" required autocomplete="off" maxlength="10">
            </div>
        </div>

        <?php if ($funerarioMode): ?>
        <div class="alert alert-light border py-2 small mb-2">
            <i class="fa fa-info-circle me-1"></i> Solo datos de contacto. El paquete, plan y enganche se registran después en <strong>Comprador funerario</strong>.
            <br><span class="text-muted">Estatus inicial: <strong>Nuevo</strong></span>
        </div>
        <?php else: ?>
        <div class="d-flex gap-2 mb-2">
            <div class="flex-fill">
                <label for="credit_amount" class="form-label small">Crédito ($) *</label>
                <input type="text" name="credit_amount" id="credit_amount" class="form-control form-control-sm currency-input" value="<?= old('credit_amount') ?>" placeholder="850,000.00">
            </div>
            <div style="width:140px;">
                <label for="zone_id" class="form-label small">Zona *</label>
                <div class="input-group">
                    <select name="zone_id" id="zone_id" class="form-control form-control-sm" required>
                        <option value="">-</option>
                        <?php foreach ($zones as $zone): ?>
                            <option value="<?= esc($zone['id']) ?>" <?= old('zone_id') == $zone['id'] ? 'selected' : '' ?>><?= esc($zone['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline-success btn-sm" type="button" onclick="addNewZone(this)">+</button>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-2">
            <div class="flex-fill">
                <label for="payment_method" class="form-label small">Método Pago *</label>
                <select name="payment_method" id="payment_method" class="form-control form-control-sm" required>
                    <option value="">-</option>
                    <?php foreach ($payment_methods as $method): ?>
                        <option value="<?= esc($method) ?>" <?= old('payment_method') == $method ? 'selected' : '' ?>><?= esc($method) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width:120px;">
                <label for="status" class="form-label small">Estatus *</label>
                <select name="status" id="status" class="form-control form-control-sm" required>
                    <?php foreach ($statuses as $s):
                        $selected = old('status') ? (old('status') == $s ? 'selected' : '') : ($s == 'Nuevo' ? 'selected' : '');
                    ?>
                        <option value="<?= esc($s) ?>" <?= $selected ?>><?= esc($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <?php if (! empty($acquisitionSources)): ?>
            <?= view('prospects/partials/acquisition_fields', [
                'acquisitionSources' => $acquisitionSources,
                'compact' => true,
            ]) ?>
        <?php endif; ?>

        <div class="text-end mt-2">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-save me-1"></i> Guardar
            </button>
        </div>

    <?= form_close() ?>
</div>

<style>
    /* Compact styles for the create form so it fits inside the offcanvas */
    .prospect-form { padding: 10px; max-width: 100%; }
    .prospect-form .header h4 { font-size: 1rem; }
    .prospect-form .alert { font-size: 0.85rem; padding: .45rem .6rem; }
    .prospect-form label.form-label.small { font-size: .75rem; margin-bottom: .25rem; }
    .prospect-form .form-control-sm { height: calc(1.5em + .5rem + 2px); font-size: .85rem; }
    .prospect-form .btn { font-size: .85rem; padding: .35rem .6rem; }
    .prospect-form .compact-form .form-group { margin-bottom: .5rem; }
    .prospect-form .d-flex.gap-2 { gap: .5rem; }
    @media (min-width: 576px) {
        .prospect-form { padding: 14px; }
    }
</style>

<!-- Inline helper: format currency when page is loaded directly (won't harm if Offcanvas injects content) -->
<script>
    (function(){
        function formatNumberToDisplay(value){
            if (value === null || value === undefined || value === '') return '';
            var num = parseFloat(String(value).replace(/[^0-9.-]/g, '')) || 0;
            return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        function unformat(value){
            if (!value) return '';
            return String(value).replace(/[^0-9.-]/g, '') || '';
        }

        function attachCurrency(el){
            if (!el) return;
            // format initial (if it's a plain number)
            if (el.value && el.value.match(/^[0-9\.]+$/)) {
                el.value = formatNumberToDisplay(el.value);
            }
            el.addEventListener('input', function(){
                // keep only digits and dot while typing
                var raw = el.value.replace(/[^0-9.\-]/g, '');
                el.value = raw;
            });
            el.addEventListener('blur', function(){ el.value = formatNumberToDisplay(el.value); });
            // on form submit, unformat
            var form = el.closest('form');
            if (form && !form.__currency_attached){
                form.addEventListener('submit', function(){
                    var inputs = form.querySelectorAll('.currency-input');
                    inputs.forEach(function(i){ i.value = unformat(i.value); });
                });
                form.__currency_attached = true;
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            var ci = document.querySelectorAll('.currency-input');
            ci.forEach(function(el){ attachCurrency(el); });
        });

        // Expose helper so parent can call it after AJAX injection
        window.initCurrencyFormatting = function(root){
            root = root || document;
            var ci = (root.querySelectorAll) ? root.querySelectorAll('.currency-input') : [];
            ci.forEach(function(el){ attachCurrency(el); });
        };
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

        document.addEventListener('DOMContentLoaded', function(){
            var phoneInputs = document.querySelectorAll('.phone-input');
            phoneInputs.forEach(function(el){ attachPhoneFormatting(el); });
        });

        // Exponer función para inicialización después de AJAX
        window.initPhoneFormatting = function(root){
            root = root || document;
            var pi = (root.querySelectorAll) ? root.querySelectorAll('.phone-input') : [];
            pi.forEach(function(el){ attachPhoneFormatting(el); });
        };
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