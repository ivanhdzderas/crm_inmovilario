<?php
$metodos = $payment_methods ?? ($payment_methods_list ?? []);
if (empty($metodos) && ! empty($nombre_metodo)) {
    $metodos = [$nombre_metodo];
}
?>
<?php if (! empty($metodos)): ?>
    <div class="payment-methods-block mb-3">
        <h6 class="text-uppercase small fw-bold text-pacific mb-2">
            <i class="fa-solid fa-credit-card me-1"></i> Formas de pago aceptadas
        </h6>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($metodos as $metodo): ?>
                <span class="badge payment-badge"><?= esc($metodo) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
