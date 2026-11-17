<?php
// Formas de pago de UN inmueble. Se pasa $payment_methods_list explícito: no se toma
// $payment_methods (en la vista padre puede ser el catálogo completo de formas de pago).
$metodos = $payment_methods_list ?? [];
if (empty($metodos) && ! empty($nombre_metodo)) {
    $metodos = [$nombre_metodo];
}
$metodos = array_values(array_filter($metodos, static fn ($m) => mb_strtolower((string) $m) !== 'todos'));
?>
<?php if (! empty($metodos)): ?>
    <div class="payment-methods-block mb-3">
        <h6 class="text-uppercase small fw-bold mb-2">
            <i class="fa-solid fa-credit-card me-1"></i> Formas de pago aceptadas
        </h6>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($metodos as $metodo): ?>
                <span class="badge payment-badge chip"><?= esc($metodo) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
