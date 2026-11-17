<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>
<?php
$hayFiltros = (bool) array_filter($filtros, static fn ($v) => $v !== null && $v !== '');
$sel = static fn ($a, $b) => (string) $a === (string) $b ? 'selected' : '';
?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta"><a href="<?= base_url('/') ?>">Inicio</a><span>/</span>Inmuebles</nav>
        <h1>Inmuebles en venta</h1>
        <p>Casas, departamentos y terrenos disponibles. Filtra por zona, precio o forma de pago.</p>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <button class="filtros-toggle mb-2" type="button" data-toggle-filtros="#filtrosCuerpo" aria-expanded="false" aria-controls="filtrosCuerpo">
            <span><i class="fa-solid fa-sliders me-2"></i>Filtrar<?= $hayFiltros ? ' (activos)' : '' ?></span><i class="fa-solid fa-chevron-down"></i>
        </button>
        <form class="filtros filtros-cuerpo <?= $hayFiltros ? 'abierto' : '' ?>" id="filtrosCuerpo" method="get" action="<?= base_url('inmuebles') ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-2">
                    <label class="form-label" for="f_zona">Zona</label>
                    <select class="form-select" id="f_zona" name="zone_id">
                        <option value="">Todas</option>
                        <?php foreach ($zonas as $z): ?>
                            <option value="<?= (int) $z['id'] ?>" <?= $sel($filtros['zone_id'], $z['id']) ?>><?= esc(ucfirst($z['name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (! empty($proyectos)): ?>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label" for="f_desarrollo">Desarrollo</label>
                    <select class="form-select" id="f_desarrollo" name="project_id">
                        <option value="">Todos</option>
                        <?php foreach ($proyectos as $pr): ?>
                            <option value="<?= (int) $pr['id'] ?>" <?= $sel($filtros['project_id'], $pr['id']) ?>><?= esc($pr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label" for="f_min">Precio</label>
                    <div class="input-group">
                        <input class="form-control" id="f_min" type="number" name="min_price" min="0" step="10000" placeholder="Mínimo" value="<?= esc($filtros['min_price'] ?? '') ?>" aria-label="Precio mínimo">
                        <input class="form-control" type="number" name="max_price" min="0" step="10000" placeholder="Máximo" value="<?= esc($filtros['max_price'] ?? '') ?>" aria-label="Precio máximo">
                    </div>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label" for="f_pago">Forma de pago</label>
                    <select class="form-select" id="f_pago" name="payment_method">
                        <option value="">Cualquiera</option>
                        <?php foreach ($formasPago as $fp): ?>
                            <option value="<?= esc($fp) ?>" <?= $sel($filtros['payment_method'], $fp) ?>><?= esc($fp) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 col-lg-3 d-flex gap-2">
                    <button class="btn btn-pac flex-grow-1" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i> Buscar</button>
                    <?php if ($hayFiltros): ?>
                        <a class="btn btn-linea" href="<?= base_url('inmuebles') ?>" title="Quitar filtros">Limpiar</a>
                    <?php endif; ?>
                </div>
            </div>
            <input type="hidden" name="orden" value="<?= esc($orden) ?>">
        </form>

        <div class="resultado-barra">
            <span><strong class="text-dark"><?= count($inmuebles) ?></strong> <?= count($inmuebles) === 1 ? 'inmueble' : 'inmuebles' ?><?= $hayFiltros ? ' con estos filtros' : '' ?></span>
            <?php if (count($inmuebles) > 1): ?>
                <form method="get" action="<?= base_url('inmuebles') ?>" class="d-flex align-items-center gap-2">
                    <?php foreach ($filtros as $k => $v): if ($v !== null && $v !== ''): ?><input type="hidden" name="<?= esc($k) ?>" value="<?= esc($v) ?>"><?php endif; endforeach; ?>
                    <label class="small" for="orden">Ordenar</label>
                    <select class="form-select form-select-sm" id="orden" name="orden" onchange="this.form.submit()" style="width:auto">
                        <option value="">Por desarrollo</option>
                        <option value="recientes" <?= $sel($orden, 'recientes') ?>>Más recientes</option>
                        <option value="precio_asc" <?= $sel($orden, 'precio_asc') ?>>Menor precio</option>
                        <option value="precio_desc" <?= $sel($orden, 'precio_desc') ?>>Mayor precio</option>
                    </select>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($inmuebles)): ?>
            <div class="vacio">
                <i class="fa-solid fa-magnifying-glass-location d-block"></i>
                <h2 class="h4">No encontramos inmuebles con esos filtros</h2>
                <p>Prueba con otro rango de precio o zona, o déjanos tus datos y te avisamos cuando haya algo para ti.</p>
                <a class="btn btn-arena" href="<?= base_url('/') ?>#contacto">Quiero que me avisen</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($inmuebles as $p): ?>
                    <div class="col-sm-6 col-lg-4"><?= view('partials/tarjeta_inmueble', ['p' => $p]) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
