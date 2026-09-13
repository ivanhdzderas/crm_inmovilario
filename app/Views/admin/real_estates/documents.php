<?php helper(['form']); ?>

<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Documentos para <?= esc($realEstate['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Documentos para <?= esc($realEstate['name']) ?></h1>
        <a href="<?= base_url('admin/real_estates') ?>" class="btn btn-secondary">Regresar a Inmuebles</a>
    </div>

    <hr>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <h3>Subir Documentos</h3>
            <p>Sube los documentos necesarios para este inmueble. Si subes un documento del mismo tipo, se reemplazará el anterior.</p>
            
            <?= form_open_multipart('admin/real_estates/documents/upload', ['class' => 'p-4 border rounded shadow-sm bg-light']) ?>
                <input type="hidden" name="real_estate_id" value="<?= esc($realEstate['id']) ?>">
                
                <div class="form-group mb-3">
                    <label for="document_type_id" class="form-label">Tipo de Documento</label>
                    <select class="form-control" name="document_type_id" id="document_type_id" required>
                        <option value="">Selecciona un tipo</option>
                        <?php foreach ($documentTypes as $type): ?>
                            <option value="<?= esc($type['id']) ?>"><?= esc($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="document_file" class="form-label">Seleccionar Archivo</label>
                    <input type="file" class="form-control" name="document_file" id="document_file" required>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">Subir Documento</button>
            <?= form_close() ?>
        </div>

        <div class="col-md-6">
            <h3>Documentos Subidos</h3>
            <?php if (empty($uploadedDocuments)): ?>
                <div class="alert alert-info">No hay documentos subidos para este inmueble.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($uploadedDocuments as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= base_url($doc['file_path']) ?>" target="_blank" class="text-decoration-none">
                                <?= esc($doc['document_name']) ?>
                            </a>
                            <a href="#" class="btn btn-danger btn-sm" onclick="confirmDeleteDocument('<?= base_url('admin/real_estates/documents/delete/' . $doc['id']) ?>', '<?= esc($doc['name']) ?>');">
                                Eliminar
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
