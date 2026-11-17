<?= $this->extend('public_layout') ?>
<?= $this->section('content') ?>
<?php
$hayFiltros = (bool) array_filter($filters ?? []);
$sel = static fn ($a, $b) => (string) $a === (string) $b ? 'selected' : '';
?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta"><a href="<?= base_url('/') ?>">Inicio</a><span>/</span>Previsión funeraria</nav>
        <span class="eyebrow">Previsión funeraria</span>
        <h1>Planes para tu tranquilidad y la de tu familia</h1>
        <p>Criptas, nichos y paquetes en distintos lugares de descanso, con planes de pago a meses.</p>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <button class="filtros-toggle mb-2" type="button" data-toggle-filtros="#filtrosFun" aria-expanded="false" aria-controls="filtrosFun">
            <span><i class="fa-solid fa-sliders me-2"></i>Filtrar<?= $hayFiltros ? ' (activos)' : '' ?></span><i class="fa-solid fa-chevron-down"></i>
        </button>
        <form class="filtros filtros-cuerpo <?= $hayFiltros ? 'abierto' : '' ?>" id="filtrosFun" method="get" action="<?= base_url('funerario') ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="fl">Lugar de descanso</label>
                    <select class="form-select" id="fl" name="location_id">
                        <option value="">Todos</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= (int) $loc['id'] ?>" <?= $sel($filters['location_id'] ?? '', $loc['id']) ?>><?= esc($loc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="fh">Funeraria</label>
                    <select class="form-select" id="fh" name="home_id">
                        <option value="">Todas</option>
                        <?php foreach ($homes as $h): ?>
                            <option value="<?= (int) $h['id'] ?>" <?= $sel($filters['home_id'] ?? '', $h['id']) ?>><?= esc($h['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (! empty($types)): ?>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="ft">Tipo</label>
                    <select class="form-select" id="ft" name="type_id">
                        <option value="">Todos</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= $sel($filters['product_type_id'] ?? '', $t['id']) ?>><?= esc($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-12 col-lg-3 d-flex gap-2">
                    <button class="btn btn-pac flex-grow-1" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i> Buscar</button>
                    <?php if ($hayFiltros): ?><a class="btn btn-linea" href="<?= base_url('funerario') ?>">Limpiar</a><?php endif; ?>
                </div>
            </div>
        </form>

        <div class="resultado-barra">
            <span><strong class="text-dark"><?= count($productos) ?></strong> <?= count($productos) === 1 ? 'paquete' : 'paquetes' ?><?= $hayFiltros ? ' con estos filtros' : '' ?></span>
        </div>

        <?php if (empty($productos)): ?>
            <div class="vacio">
                <i class="fa-solid fa-dove d-block"></i>
                <h2 class="h4">No hay paquetes con esos filtros</h2>
                <p>Escríbenos y un asesor te ayudará a encontrar la mejor opción.</p>
                <a class="btn btn-arena" href="<?= base_url('/') ?>#contacto">Quiero información</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($productos as $p): ?>
                    <div class="col-sm-6 col-lg-4"><?= view('partials/tarjeta_paquete', ['p' => $p]) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
