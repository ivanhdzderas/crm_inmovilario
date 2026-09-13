<?php
/** @var list<array> $acquisitionSources */
$selectedId = $selectedId ?? old('acquisition_source_id');
$detail = $detail ?? old('acquisition_detail', '');
$compact = $compact ?? false;
?>
<div class="mb-2">
    <label for="acquisition_source_id" class="form-label <?= $compact ? 'small' : '' ?>">¿Cómo llegó? <?= $compact ? '' : '*' ?></label>
    <select name="acquisition_source_id" id="acquisition_source_id" class="form-control <?= $compact ? 'form-control-sm' : '' ?>" <?= $compact ? '' : 'required' ?>>
        <option value="">-- Seleccionar origen --</option>
        <?php foreach ($acquisitionSources as $src): ?>
            <option value="<?= (int) $src['id'] ?>" <?= (string) $selectedId === (string) $src['id'] ? 'selected' : '' ?>>
                <?= esc($src['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-2">
    <label for="acquisition_detail" class="form-label <?= $compact ? 'small' : '' ?>">Detalle (opcional)</label>
    <input type="text" name="acquisition_detail" id="acquisition_detail" class="form-control <?= $compact ? 'form-control-sm' : '' ?>"
        value="<?= esc($detail) ?>" placeholder="Ej. reel de Instagram, referido por Juan, anuncio de Facebook...">
</div>
