<?= $this->extend('template') ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h1 class="card-title text-center">Añadir Nuevo Proyecto Inmobiliario</h1>
    </div>
    <div class="card-body">
        <form action="<?= base_url('admin/proyectos/store'); ?>" method="post">
            <?= csrf_field(); ?>

            <div class="form-group mb-3">
                <label for="name">Nombre del Proyecto</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="form-group mb-3">
                <label for="description">Descripción</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="location">Ubicación</label>
                <input type="text" class="form-control" id="location" name="location">
            </div>

            <button type="submit" class="btn btn-primary">Guardar Proyecto</button>
            <a href="<?= base_url('admin/proyectos'); ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>