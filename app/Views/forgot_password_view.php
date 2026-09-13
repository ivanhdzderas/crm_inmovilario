<?= $this->extend('template_auth') ?>

<?= $this->section('title') ?>
Recuperar Contraseña
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="text-center">Recuperar Contraseña</h1>
<hr>

<p style="margin-bottom: 1rem; color: #666;">Ingresa tu correo y define una nueva contraseña.</p>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-error" style="margin-bottom: 1rem;">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= session()->getFlashdata('error') ?></span>
    </div>
<?php endif; ?>

<?php if (isset($validation)): ?>
    <div style="color: #b42318; margin-bottom: 1rem;"><?= $validation->listErrors() ?></div>
<?php endif; ?>

<?= form_open('forgot-password') ?>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">
            <i class="fas fa-envelope"></i> Correo Electrónico
        </label>
        <div class="input-wrapper">
            <i class="input-icon fas fa-user"></i>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                value="<?= old('email') ?>"
                placeholder="tu@email.com"
                autocomplete="email"
                required>
        </div>
    </div>

    <div class="form-group">
        <label for="password">
            <i class="fas fa-lock"></i> Nueva Contraseña
        </label>
        <div class="input-wrapper">
            <i class="input-icon fas fa-key"></i>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Mínimo 8 caracteres"
                autocomplete="new-password"
                required>
        </div>
    </div>

    <div class="form-group">
        <label for="password_confirm">
            <i class="fas fa-lock"></i> Confirmar Nueva Contraseña
        </label>
        <div class="input-wrapper">
            <i class="input-icon fas fa-key"></i>
            <input
                type="password"
                id="password_confirm"
                name="password_confirm"
                class="form-control"
                placeholder="Repite la nueva contraseña"
                autocomplete="new-password"
                required>
        </div>
    </div>

    <button type="submit" class="btn">
        <i class="fas fa-save"></i>
        <span>Actualizar Contraseña</span>
    </button>
</form>

<div style="margin-top: 1rem; text-align: center;">
    <a href="<?= base_url('login') ?>">Volver al inicio de sesión</a>
</div>

<?= $this->endSection() ?>
