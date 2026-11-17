<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Editar Usuario
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h2">Editar Usuario</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (isset($validation)): ?>
        <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?= form_open('admin/users/update') ?>
                <input type="hidden" name="id" value="<?= esc($user['id']) ?>">
                
                <div class="mb-3">
                    <label for="full_name" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= esc($user['full_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= esc($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= esc($user['phone'] ?? '') ?>">
                    <small class="text-muted">Este teléfono aparecerá en las fichas PDF.</small>
                </div>

                <div class="mb-3">
                    <label for="role_id" class="form-label">Rol</label>
                    <select class="form-control" id="role_id" name="role_id">
                        <option value="1" <?= $user['role_id'] == 1 ? 'selected' : '' ?>>Admin</option>
                        <option value="2" <?= $user['role_id'] == 2 ? 'selected' : '' ?>>Asesor</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">Estado de la cuenta</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="1" <?= (int) $user['is_active'] === 1 ? 'selected' : '' ?>>Activa (puede entrar)</option>
                        <option value="0" <?= (int) $user['is_active'] !== 1 ? 'selected' : '' ?>>Inactiva / pendiente de aprobación</option>
                    </select>
                    <small class="text-muted">Las cuentas creadas desde "Registrarse" quedan pendientes hasta que las actives aquí.</small>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Nueva Contraseña (opcional)</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="text-muted">Dejar en blanco para no cambiar la contraseña actual.</small>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">Cancelar</a>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>