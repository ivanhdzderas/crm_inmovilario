<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ficha Tecnica</title>

<style>
@page { margin: 10mm; }

body {
  font-family: DejaVu Sans, sans-serif;
  font-size: 11px;
  color: #6B6B6B;
}

/* HEADER */
.hdr {
  border-bottom:1px solid #E8E8E8;
  margin-bottom:8px;
  padding-bottom:6px;
}
.hdr table { width:100%; }

.logo-img { max-width:110px; }
.logo-txt {
  font-size:16px;
  letter-spacing:3px;
  color:#C5A46D;
  font-weight:bold;
}

.hdr-contact {
  text-align:right;
  font-size:9px;
  line-height:1.5;
  color:#5F6B75;
}

/* FOOTER */
.ftr {
  position:fixed;
  bottom:-10mm;
  left:-10mm;
  right:-10mm;
  height:10mm;
  background:#1E2A38;
}

/* GALERÍA */
.gal-main { width:68%; padding-right:8px; }
.gal-main img { width:100%; height:230px; }

.gal-side { width:32%; }
.gal-side img { width:100%; height:110px; }

/* INFO */
.ref-lbl {
  color:#C5A46D;
  font-size:10px;
  letter-spacing:2px;
}

.prop-title {
  font-size:24px;
  font-weight:bold;
  color:#1E2A38;
  margin:4px 0;
}

.prop-sub {
  font-size:11px;
  letter-spacing:2px;
  color:#7D8892;
}

/* PRECIO */
.price-box {
  border-left:2px solid #C5A46D;
  padding-left:12px;
}

.price-lbl {
  font-size:10px;
  color:#C5A46D;
}

.price-val {
  font-size:20px;
  font-weight:bold;
  color:#1E2A38;
}

/* FEATURES */
.feat-wrap {
  border-top:1px solid #E8E8E8;
  border-bottom:1px solid #E8E8E8;
  margin:12px 0;
}
.feat-wrap table { width:100%; }
.feat-wrap td {
  text-align:center;
  padding:10px 4px;
  border-right:1px solid #E8E8E8;
}
.feat-wrap td:last-child { border-right:none; }

.feat-val {
  font-size:14px;
  font-weight:bold;
  color:#1E2A38;
}
.feat-lbl {
  font-size:9px;
  color:#7A7A7A;
}

/* TITULOS */
.sec-title {
  font-size:10px;
  letter-spacing:2px;
  color:#C5A46D;
}

/* LINEA */
.sec-title-row { width:100%; margin:12px 0 8px 0; }
.sec-line { border-bottom:1px solid #E8E8E8; }

/* CARACTERISTICAS */
.chars-table { width:100%; margin-bottom:12px; }
.chars-table td {
  width:25%;
  padding:3px 5px;
  font-size:10px;
  color:#555;
}

/* DESCRIPCIÓN */
.desc-txt {
  font-size:11px;
  line-height:1.6;
  color:#5F6B75;
  padding-right:15px;
}
.desc-img img {
  width:170px;
  height:120px;
}

/* UBICACIÓN */
.loc-map img {
  width:170px;
  height:130px;
}
.loc-txt {
  font-size:11px;
  line-height:1.6;
  color:#5F6B75;
}
</style>
</head>

<body>

<?php
function embed_image($path) {
    if (!$path || !file_exists($path) || !is_readable($path)) return null;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext === 'jpg') $ext = 'jpeg';
    return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($path));
}

function normalize_credit_type_icon_name($type) {
  $type = strtolower(trim((string) $type));
  $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $type);
  if ($trans !== false) {
    $type = $trans;
  }

  $type = preg_replace('/[^a-z0-9]+/', '_', $type);
  $type = trim((string) $type, '_');

  return $type !== '' ? $type : 'credito';
}

function payment_icon($type) {
  $baseName = normalize_credit_type_icon_name($type);
  $extensions = ['png', 'jpg', 'jpeg', 'webp'];
  $baseDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'icons';

  foreach ($extensions as $ext) {
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . $baseName . '.' . $ext;
    if (file_exists($fullPath) && is_readable($fullPath)) {
      $mime = $ext === 'jpg' ? 'jpeg' : $ext;
      $data = base64_encode(file_get_contents($fullPath));
      return 'data:image/' . $mime . ';base64,' . $data;
    }
  }

  return null;
}

$photos = $realEstate['photos'] ?? [];
?>

<?php if (!empty($show_branding)): ?>
<div class="hdr">
  <table>
    <tr>
      <td>
        <?php $logo = embed_image(FCPATH . 'img/logo.png'); ?>
        <?php if ($logo): ?>
          <img src="<?= $logo ?>" class="logo-img">
        <?php else: ?>
          <span class="logo-txt"><?= esc($company_name) ?></span>
        <?php endif; ?>
      </td>
      <td class="hdr-contact">
        <strong><?= esc($company_website ?? '') ?></strong><br>
        <?= esc($company_phone ?? '') ?><br>
        <?= esc($company_address ?? '') ?><br>
        <?= esc($company_email ?? '') ?>
      </td>
    </tr>
  </table>
</div>

<div class="ftr"></div>
<?php endif; ?>

<!-- GALERÍA -->
<?php if (!empty($photos)): ?>
<table style="width:100%; margin-bottom:12px;">
<tr>
<td class="gal-main">
<?php if ($img = embed_image(FCPATH . $photos[0]['file_path'])): ?>
<img src="<?= $img ?>">
<?php endif; ?>
</td>

<?php if (isset($photos[1])): ?>
<td class="gal-side">
<?php if ($img = embed_image(FCPATH . $photos[1]['file_path'])): ?>
<img src="<?= $img ?>" style="margin-bottom:8px;">
<?php endif; ?>

<?php if (isset($photos[2]) && ($img = embed_image(FCPATH . $photos[2]['file_path']))): ?>
<img src="<?= $img ?>">
<?php endif; ?>
</td>
<?php endif; ?>
</tr>
</table>
<?php endif; ?>

      $raw = curl_exec($ch);
<table style="width:100%; border-bottom:1px solid #E8E8E8;">
<tr>

<td>
<div class="ref-lbl">REFERENCIA: <?= esc($realEstate['clave'] ?? '') ?></div>
<div class="prop-title"><?= esc($realEstate['name']) ?></div>
<div class="prop-sub"><?= esc($realEstate['address']) ?></div>
</td>

<td style="width:190px;">
<div class="price-box">
  <div class="price-lbl">PRECIO DE VENTA</div>
  <div class="price-val">$<?= number_format($realEstate['price'], 0) ?> MXN</div>

  <!-- MÉTODOS DE PAGO -->
  <?php if (!empty($realEstate['payment_methods'])): ?>
  <div style="margin-top:6px;">
    <div style="font-size:8px; color:#C5A46D;">FORMAS DE PAGO</div>

    <?php 
    $methodsRaw = $realEstate['payment_methods'];
    if (is_array($methodsRaw)) {
      $methods = $methodsRaw;
    } else {
      $decoded = json_decode((string) $methodsRaw, true);
      if (is_array($decoded)) {
        $methods = $decoded;
      } else {
        $methods = explode(',', (string) $methodsRaw);
      }
    }
    $methods = array_values(array_filter(array_map('trim', $methods), static fn($m) => $m !== ''));

    foreach ($methods as $m):
      $icon = payment_icon($m);
    ?>
      <span style="
        display:inline-block;
        border:1px solid #C5A46D;
        padding:2px 6px;
        margin:2px 3px 0 0;
        font-size:7px;
        color:#1E2A38;
      ">
        <?php if ($icon): ?>
          <img src="<?= $icon ?>" style="height:10px; vertical-align:middle; margin-right:3px;">
        <?php endif; ?>
        <?= trim($m) ?>
      </span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</td>

</tr>
</table>

<?php
$allAmenities = $realEstate['amenities'] ?? [];

$normalizeText = static function (string $text): string {
  $text = strtolower(trim($text));
  $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
  return $ascii !== false ? $ascii : $text;
};

$findStatAmenity = static function(array $keywords) use ($allAmenities, $normalizeText): ?array {
  foreach ($allAmenities as $am) {
    $type = (string)($am['amenity_type'] ?? '');
    $typeNorm = $normalizeText($type);
    foreach ($keywords as $kw) {
      $kwNorm = $normalizeText((string) $kw);
      if ($kwNorm !== '' && strpos($typeNorm, $kwNorm) !== false) {
        return [
          'value' => trim((string)($am['value'] ?? '')),
          'type_norm' => $typeNorm,
        ];
      }
    }
  }
  return null;
};

$beds = $findStatAmenity(['recamara', 'cuarto', 'habitacion', 'dormitorio']);
$baths = $findStatAmenity(['bano', 'sanitario', 'wc']);
$park  = $findStatAmenity(['estacionamiento', 'cajon', 'parking', 'garaje', 'garage']);
$area  = $findStatAmenity(['area', 'superficie', 'm2', 'metro', 'construida', 'terreno']);

$svgBed  = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#C5A46D"><path d="M21 10.78V8c0-1.65-1.35-3-3-3h-4c-.77 0-1.47.3-2 .78C11.47 5.3 10.77 5 10 5H6C4.35 5 3 6.35 3 8v2.78C1.79 11.18 1 12.5 1 14v5h2v2h2v-2h12v2h2v-2h2v-5c0-1.5-.79-2.82-2-3.22zM14 8h4c.55 0 1 .45 1 1v2h-6V9c0-.55.45-1 1-1zM6 8h4c.55 0 1 .45 1 1v2H5V9c0-.55.45-1 1-1zm15 9H3v-3c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2v3z"/></svg>');
$svgBath = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#C5A46D"><path d="M21 10H7V7c0-.55.45-1 1-1s1 .45 1 1h2c0-1.66-1.34-3-3-3S5 5.34 5 7v3H3c-.55 0-1 .45-1 1v2c0 2.76 2.24 5 5 5h.73L6 21h2.46l1-3h5.08l1 3H18l-1.73-3H17c2.76 0 5-2.24 5-5v-2c0-.55-.45-1-1-1z"/></svg>');
$svgCar  = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#C5A46D"><path d="M17.5 4h-11L4 10v8h2v2h2v-2h8v2h2v-2h2v-8l-2.5-6zm0 11c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-11 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm1.08-7l1.5-4h7.84l1.5 4H7.58z"/></svg>');
$svgArea = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#C5A46D"><path d="M3 3h6v2H5v4H3V3zm12 0h6v6h-2V5h-4V3zm0 18v-2h4v-4h2v6h-6zM3 15H5v4h4v2H3v-6z"/></svg>');

$keyStats = [];
if ($beds !== null)  $keyStats[] = ['icon' => $svgBed,  'val' => $beds['value'],  'lbl' => 'Rec&aacute;maras',      'type_norm' => $beds['type_norm']];
if ($baths !== null) $keyStats[] = ['icon' => $svgBath, 'val' => $baths['value'], 'lbl' => 'Ba&ntilde;os',        'type_norm' => $baths['type_norm']];
if ($park !== null)  $keyStats[] = ['icon' => $svgCar,  'val' => $park['value'],  'lbl' => 'Estacionamientos', 'type_norm' => $park['type_norm']];
if ($area !== null)  $keyStats[] = ['icon' => $svgArea, 'val' => $area['value'],  'lbl' => 'Superficie',       'type_norm' => $area['type_norm']];
?>

<?php if (!empty($keyStats)): ?>
<table style="width:100%; margin:10px 0 0 0; border-top:1px solid #E8E8E8; border-bottom:1px solid #E8E8E8;">
<tr>
<?php foreach ($keyStats as $i => $stat): ?>
<td style="text-align:center; padding:10px 4px; <?= $i < count($keyStats) - 1 ? 'border-right:1px solid #E8E8E8;' : '' ?> vertical-align:middle;">
  <img src="<?= $stat['icon'] ?>" style="width:24px; height:24px; display:block; margin:0 auto 6px auto;">
  <span style="font-size:15px; font-weight:bold; color:#1E2A38; display:block;"><?= esc($stat['val']) ?></span>
  <span style="font-size:9px; color:#7A7A7A;"><?= $stat['lbl'] ?></span>
</td>
<?php endforeach; ?>
</tr>
</table>
<?php endif; ?>

<!-- AMENITIES STRIP -->
<?php
$selectedTypeNorms = array_values(array_unique(array_filter(array_map(static fn($s) => $s['type_norm'] ?? '', $keyStats))));
$amenities = array_values(array_filter($allAmenities, static function ($am) use ($normalizeText, $selectedTypeNorms) {
  $typeNorm = $normalizeText((string) ($am['amenity_type'] ?? ''));
  return !in_array($typeNorm, $selectedTypeNorms, true);
}));
$amenities = array_slice($amenities, 0, 6);
if (!empty($amenities)):
?>
<div class="feat-wrap">
<table>
<tr>
<?php foreach ($amenities as $am): ?>
<td>
<span class="feat-val"><?= esc($am['value']) ?></span>
<span class="feat-lbl"><?= esc($am['amenity_type']) ?></span>
</td>
<?php endforeach; ?>
</tr>
</table>
</div>
<?php endif; ?>

<!-- DESCRIPCIÓN -->
<table class="sec-title-row">
<tr>
<td style="width:120px;"><div class="sec-title">DESCRIPCIÓN</div></td>
<td class="sec-line"></td>
</tr>
</table>

<div style="width:100%;">
<?php if (isset($photos[3]) && ($img = embed_image(FCPATH . $photos[3]['file_path']))): ?>
  <img src="<?= $img ?>" style="float:right; width:170px; height:120px; margin-left:12px; margin-bottom:6px;">
<?php endif; ?>
<div class="desc-txt" style="padding-right:0;">
<?= nl2br(esc($realEstate['description_general'])) ?>
</div>
<div style="clear:both;"></div>
</div>

<!-- UBICACIÓN -->
<?php
$mapsKey = env('GOOGLE_MAPS_API_KEY', '');
$gmLink = trim((string) ($realEstate['google_maps_link'] ?? ''));
$fallbackAddress = trim((string) ($realEstate['address'] ?? ''));

$mapsUrl = null;
$centerFromLink = '';

$resolveCoordinatesFromText = static function (string $text): ?string {
  $text = trim($text);
  if ($text === '') {
    return null;
  }

  $base = $text;
  $norm = preg_replace('/\bGral\.?\b/ui', 'General', $base);
  $norm = preg_replace('/\bNay\.?\b/ui', 'Nayarit', $norm);
  $norm = preg_replace('/\bSin\s+Nombre\s+Loc\.?\s*/ui', '', $norm);
  $norm = preg_replace('/\bLoc\.?\s*/ui', '', $norm);
  $norm = preg_replace('/\s*,\s*/', ', ', $norm);
  $norm = preg_replace('/\s+/', ' ', $norm);
  $norm = trim((string) $norm, " ,");

  $candidates = [$base, $norm];

  $withoutPostal = preg_replace('/\b\d{5}\b/', '', $norm);
  $withoutPostal = trim((string) preg_replace('/\s*,\s*/', ', ', (string) $withoutPostal), " ,");
  if ($withoutPostal !== '') {
    $candidates[] = $withoutPostal;
  }

  // Intento simplificado: calle + numero + ciudad/estado/pais
  $parts = array_values(array_filter(array_map('trim', explode(',', $withoutPostal ?: $norm))));
  if (count($parts) >= 2) {
    $first = $parts[0];
    $city = '';
    foreach ($parts as $p) {
      if (stripos($p, 'san blas') !== false) {
        $city = 'San Blas';
        break;
      }
    }
    $simple = $first . ', ' . ($city !== '' ? $city . ', ' : '') . 'Nayarit, Mexico';
    $candidates[] = $simple;
  }

  if (stripos($norm, 'mexico') === false) {
    $candidates[] = $norm . ', Mexico';
  }

  $candidates = array_values(array_unique(array_filter(array_map('trim', $candidates))));

  foreach ($candidates as $candidate) {
    $endpoint = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($candidate);

    // Intento con cURL si está disponible
    if (function_exists('curl_init')) {
      $ch = curl_init($endpoint);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 6);
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Tony-CRM/1.0 (PDF geocoding)');
      $raw = curl_exec($ch);
      $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($raw !== false && $code === 200) {
        $payload = json_decode($raw, true);
        if (is_array($payload) && !empty($payload[0]['lat']) && !empty($payload[0]['lon'])) {
          return trim((string) $payload[0]['lat']) . ',' . trim((string) $payload[0]['lon']);
        }
      }
    }

    // Fallback con file_get_contents
    $context = stream_context_create([
      'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Tony-CRM/1.0 (PDF geocoding)\r\nAccept: application/json\r\n",
        'timeout' => 6,
      ],
    ]);

    $raw = @file_get_contents($endpoint, false, $context);
    if ($raw !== false) {
      $payload = json_decode($raw, true);
      if (is_array($payload) && !empty($payload[0]['lat']) && !empty($payload[0]['lon'])) {
        return trim((string) $payload[0]['lat']) . ',' . trim((string) $payload[0]['lon']);
      }
    }
  }

  return null;
};

if ($gmLink !== '') {
  $query = parse_url($gmLink, PHP_URL_QUERY);
  if (!empty($query)) {
    parse_str($query, $params);
    if (!empty($params['q'])) {
      $centerFromLink = trim((string) $params['q']);
    }
    if ($centerFromLink === '' && !empty($params['ll'])) {
      $centerFromLink = trim((string) $params['ll']);
    }
  }

  if ($centerFromLink === '' && preg_match('/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/', $gmLink, $m)) {
    $centerFromLink = $m[1] . ',' . $m[2];
  }
}

$center = $centerFromLink !== '' ? $centerFromLink : $fallbackAddress;

// 1) Si hay coordenadas, usar OpenStreetMap (sin API key)
if (preg_match('/^-?\d{1,3}\.\d+\s*,\s*-?\d{1,3}\.\d+$/', $center)) {
  $coord = preg_replace('/\s+/', '', $center);
  [$lat, $lng] = explode(',', $coord);
  $mapsUrl = 'https://static-maps.yandex.ru/1.x/?lang=es_ES&ll=' . $lng . ',' . $lat . '&z=15&size=340,260&l=map&pt=' . $lng . ',' . $lat . ',pm2rdm';
}
// 2) Si es texto, intentar geocodificar a coordenadas con OSM y usar mapa sin key
elseif ($center !== '') {
  $coord = $resolveCoordinatesFromText($center);
  if ($coord !== null) {
    $coord = preg_replace('/\s+/', '', $coord);
    [$lat, $lng] = explode(',', $coord);
    $mapsUrl = 'https://static-maps.yandex.ru/1.x/?lang=es_ES&ll=' . $lng . ',' . $lat . '&z=15&size=340,260&l=map&pt=' . $lng . ',' . $lat . ',pm2rdm';
  }
}
// 2) Si es texto dirección, usar Google Static Maps solo si hay key
if ($mapsUrl === null && $center !== '' && $mapsKey !== '') {
  $mapsUrl = 'https://maps.googleapis.com/maps/api/staticmap?center=' . urlencode($center) . '&zoom=15&size=340x260&markers=color:orange%7C' . urlencode($center) . '&key=' . $mapsKey;
}
?>

<table class="sec-title-row">
<tr>
<td style="width:200px;"><div class="sec-title">UBICACIÓN PRIVILEGIADA</div></td>
<td class="sec-line"></td>
</tr>
</table>

<table style="width:100%;">
<tr>

<?php if ($mapsUrl): ?>
<td class="loc-map">
<img src="<?= esc($mapsUrl) ?>">
</td>
<?php elseif (!empty($gmLink)): ?>
<td class="loc-map" style="vertical-align:top;">
  <div style="width:170px; height:130px; border:1px solid #E8E8E8; padding:8px; font-size:9px; color:#777; line-height:1.3;">
    No se pudo renderizar el mapa est&aacute;tico para esta ubicaci&oacute;n.
  </div>
</td>
<?php endif; ?>

<td class="loc-txt">
<?= nl2br(esc($realEstate['project_description'] ?? 'Ubicación privilegiada con acceso a zonas clave.')) ?>
</td>

</tr>
</table>

</body>
</html>