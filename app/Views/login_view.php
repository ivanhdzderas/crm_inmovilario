<?= $this->extend('template_auth') ?>

<?= $this->section('title') ?>
Iniciar Sesión
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="text-center">Iniciar Sesión</h1>
<hr>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= session()->getFlashdata('error') ?></span>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-success" style="margin-bottom: 1rem;">
        <i class="fas fa-check-circle"></i>
        <span><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif; ?>

<?= form_open('login/auth') ?>
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
            <i class="fas fa-lock"></i> Contraseña
        </label>
        <div class="input-wrapper">
            <i class="input-icon fas fa-key"></i>
            <input 
                type="password" 
                id="password"
                name="password" 
                class="form-control" 
                placeholder="••••••••"
                autocomplete="current-password"
                required>
            <button type="button" class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
        </div>
        <div style="text-align: right; margin-top: 0.5rem;">
            <a href="<?= base_url('forgot-password') ?>">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
    
    <button type="submit" class="btn">
        <i class="fas fa-sign-in-alt"></i>
        <span>Iniciar Sesión</span>
    </button>
</form>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Prevenir zoom en iOS al hacer focus en inputs
if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.fontSize = '16px';
        });
    });
}
</script>

<?= $this->endSection() ?>