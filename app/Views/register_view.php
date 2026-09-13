<?= $this->extend('template_auth') ?>

<?= $this->section('title') ?>
Registro
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <h1 class="text-center">Registro de Usuario</h1>
    <hr>
    <?php if (isset($validation)): ?>
        <div style="color: red; margin-bottom: 20px;"><?= $validation->listErrors() ?></div>
    <?php endif; ?>

    <?= form_open('register/store') ?>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="full_name">Nombre Completo:</label>
            <input type="text" name="full_name" class="form-control" value="<?= old('full_name') ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar Contraseña:</label>
            <input type="password" name="password_confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button>
    </form>
</div>
<?= $this->endSection() ?>