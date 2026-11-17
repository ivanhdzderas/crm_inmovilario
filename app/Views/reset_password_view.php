<?= $this->extend('template_auth') ?>

<?= $this->section('title') ?>
Nueva Contraseña
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="text-center">Nueva Contraseña</h1>
<hr>

<p style="margin-bottom: 1rem; color: #666;">Escribe tu nueva contraseña (mínimo 8 caracteres).</p>

<?php if (isset($validation)): ?>
    <div style="color: #b42318; margin-bottom: 1rem;"><?= $validation->listErrors() ?></div>
<?php endif; ?>

<?= form_open('restablecer/' . $token) ?>
    <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Nueva Contraseña</label>
        <div class="input-wrapper">
            <i class="input-icon fas fa-key"></i>
            <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" autocomplete="new-password" minlength="8" required>
        </div>
    </div>

    <div class="form-group">
        <label for="password_confirm"><i class="fas fa-lock"></i> Confirmar Contraseña</label>
        <div class="input-wrapper">
            <i class="input-icon fas fa-key"></i>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Repite la contraseña" autocomplete="new-password" minlength="8" required>
        </div>
    </div>

    <button type="submit" class="btn">
        <i class="fas fa-save"></i>
        <span>Guardar contraseña</span>
    </button>
</form>

<div style="margin-top: 1rem; text-align: center;">
    <a href="<?= base_url('login') ?>">Volver al inicio de sesión</a>
</div>

<?= $this->endSection() ?>
