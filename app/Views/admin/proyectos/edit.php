<?= $this->extend('template'); ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header">
        <h1 class="card-title text-center">Editar Proyecto</h1>
    </div>
    <div class="card-body">
<form action="<?= base_url('admin/proyectos/update/' . esc($proyecto['id'])); ?>" method="post">
            <?= csrf_field(); ?>
            <input type="hidden" name="_method" value="PUT">

            <div class="form-group mb-3">
                <label for="name">Nombre del Proyecto</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= esc($proyecto['name']); ?>" required>
            </div>

            <div class="form-group mb-3">
                <label for="description">Descripción</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= esc($proyecto['description']); ?></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="location">Ubicación</label>
                <input type="text" class="form-control" id="location" name="location" value="<?= esc($proyecto['location']); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="<?= base_url('admin/proyectos'); ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>