<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Configuración del Sistema
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-cogs text-primary"></i> Configuración del Sistema</h1>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?= form_open('admin/settings/update') ?>
                
                <h5 class="card-title">Configuración General</h5>
                <p class="card-text">Ajustes para la información de la empresa que aparece en los documentos.</p>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="company_name" class="form-label">Nombre de la Inmobiliaria</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= esc($settings['company_name'] ?? '') ?>">
                        <small class="text-muted">Este nombre aparecerá en las fichas PDF.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="company_website" class="form-label">Sitio Web de la Inmobiliaria</label>
                        <input type="text" class="form-control" id="company_website" name="company_website" value="<?= esc($settings['company_website'] ?? '') ?>">
                        <small class="text-muted">Esta URL aparecerá en el pie de página de las fichas PDF.</small>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="card-title">Sitio público</h5>
                <p class="card-text">Datos que ven los visitantes en la página. Los datos de contacto son opcionales: si los dejas vacíos no se muestran y los interesados usan el formulario "Me interesa", que llega como prospecto al CRM.</p>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="site_name" class="form-label">Nombre en el sitio</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" maxlength="80" value="<?= esc(old('site_name', $settings['site_name'] ?? 'Pacific Inmobiliaria')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="site_tagline" class="form-label">Lema / ubicación</label>
                        <input type="text" class="form-control" id="site_tagline" name="site_tagline" maxlength="120" value="<?= esc(old('site_tagline', $settings['site_tagline'] ?? '')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="site_leads_user_id" class="form-label">Asesor que recibe los prospectos del sitio</label>
                        <?php $leadsUser = (string) old('site_leads_user_id', $settings['site_leads_user_id'] ?? ''); ?>
                        <select class="form-control" id="site_leads_user_id" name="site_leads_user_id">
                            <option value="">Automático (primer administrador)</option>
                            <?php foreach ($asesores as $a): ?>
                                <option value="<?= (int) $a['id'] ?>" <?= $leadsUser === (string) $a['id'] ? 'selected' : '' ?>><?= esc($a['full_name']) ?><?= (int) $a['role_id'] === 1 ? ' (admin)' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Los formularios del sitio crean el prospecto asignado a esta persona (origen "Página web / Catálogo").</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="site_whatsapp" class="form-label">WhatsApp público (opcional)</label>
                        <input type="tel" class="form-control" id="site_whatsapp" name="site_whatsapp" placeholder="Vacío = no mostrar" value="<?= esc(old('site_whatsapp', $settings['site_whatsapp'] ?? '')) ?>">
                        <small class="text-muted">Si lo capturas, aparece un botón de WhatsApp en el sitio. Usa un número de la empresa, no uno personal.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="site_email" class="form-label">Correo público (opcional)</label>
                        <input type="email" class="form-control" id="site_email" name="site_email" value="<?= esc(old('site_email', $settings['site_email'] ?? '')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="site_address" class="form-label">Dirección de oficina (opcional)</label>
                        <input type="text" class="form-control" id="site_address" name="site_address" maxlength="200" value="<?= esc(old('site_address', $settings['site_address'] ?? '')) ?>">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="card-title">Configuración de Documentos</h5>
                <p class="card-text">Define qué documentos se deben solicitar según el tipo de inmueble.</p>

                <div class="form-group">
                    <label for="document_requirement_type">Requerir todos los documentos para:</label>
                    <select class="form-control" id="document_requirement_type" name="document_requirement_type">
                        <option value="all" <?= ($settings['document_requirement_type'] ?? 'all') === 'all' ? 'selected' : '' ?>>Todos los inmuebles</option>
                        <option value="new" <?= ($settings['document_requirement_type'] ?? '') === 'new' ? 'selected' : '' ?>>Solo inmuebles nuevos</option>
                        <option value="used" <?= ($settings['document_requirement_type'] ?? '') === 'used' ? 'selected' : '' ?>>Solo inmuebles usados</option>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>