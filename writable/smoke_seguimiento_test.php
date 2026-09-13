<?php

/**
 * Smoke test HTTP: formulario unificado de seguimiento.
 * Uso: php writable/smoke_seguimiento_test.php
 */

declare(strict_types=1);

$_SERVER['CI_ENVIRONMENT'] = $_ENV['CI_ENVIRONMENT'] ?? 'development';
define('ENVIRONMENT', $_SERVER['CI_ENVIRONMENT']);

if (ENVIRONMENT !== 'development') {
    fwrite(STDERR, "FAIL: smoke test solo permitido en development.\n");
    exit(1);
}

define('FCPATH', __DIR__ . '/../public/');
chdir(__DIR__ . '/..');

require FCPATH . '../vendor/autoload.php';

$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootConsole($paths);

$db = Config\Database::connect();
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$cookieJar = tempnam(sys_get_temp_dir(), 'tony_smoke_');

function fail(string $msg): never
{
    global $cookieJar;
    if (is_file($cookieJar)) {
        @unlink($cookieJar);
    }
    fwrite(STDERR, "FAIL: {$msg}\n");
    exit(1);
}

function pass(string $msg): void
{
    echo "PASS: {$msg}\n";
}

function httpRequest(string $method, string $url, array $opts = []): array
{
    global $cookieJar;

    $ch = curl_init($url);
    $headers = $opts['headers'] ?? [];
    $postFields = $opts['post'] ?? null;
    $follow = $opts['follow'] ?? true;

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => $headers,
    ]);

    if ($postFields !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    }

    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ctype = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        fail('cURL error: ' . $err);
    }

    return ['code' => $code, 'body' => (string) $body, 'ctype' => $ctype];
}

$client = $db->table('clients')
    ->whereIn('status', ['Prospecto', 'Seguimiento'])
    ->orderBy('id', 'ASC')
    ->get(1)
    ->getRowArray();

if (! $client) {
    fail('No hay prospecto en estado Prospecto/Seguimiento.');
}

$clientId = (int) $client['id'];

$user = $db->table('users')->orderBy('id', 'ASC')->get(1)->getRowArray();
if (! $user || empty($user['email'])) {
    fail('No hay usuario con email en la base de datos.');
}

$userId = (int) $user['id'];
$originalPasswordHash = $user['password_hash'] ?? null;
$tempPass = 'SmokeTest!' . random_int(10000, 99999);
$db->table('users')->where('id', $userId)->update([
    'password_hash' => password_hash($tempPass, PASSWORD_DEFAULT),
]);

register_shutdown_function(static function () use ($db, $userId, $originalPasswordHash): void {
    if ($originalPasswordHash) {
        $db->table('users')->where('id', $userId)->update(['password_hash' => $originalPasswordHash]);
    }
});

$existingPropertyIds = array_map(
    static fn ($r) => (int) $r['real_estate_id'],
    $db->table('client_real_estate_interests')->where('client_id', $clientId)->get()->getResultArray()
);

$property = $db->table('real_estates')
    ->whereIn('status', ['Disponible', 'Nuevo'])
    ->whereNotIn('id', $existingPropertyIds ?: [0])
    ->orderBy('id', 'ASC')
    ->get(1)
    ->getRowArray();

$existingFuneralIds = [];
if ($db->tableExists('client_funeral_interests')) {
    $existingFuneralIds = array_map(
        static fn ($r) => (int) $r['funeral_product_id'],
        $db->table('client_funeral_interests')->where('client_id', $clientId)->get()->getResultArray()
    );
}

$funeral = null;
if ($db->tableExists('funeral_products')) {
    $funeral = $db->table('funeral_products')
        ->where('status', 'Disponible')
        ->whereNotIn('id', $existingFuneralIds ?: [0])
        ->orderBy('id', 'ASC')
        ->get(1)
        ->getRowArray();
}

$followUpsBefore = (int) $db->table('client_follow_ups')->where('client_id', $clientId)->countAllResults();
$activitiesBefore = (int) $db->table('calendar_activities')->where('client_id', $clientId)->countAllResults();
$propertyInterestsBefore = (int) $db->table('client_real_estate_interests')->where('client_id', $clientId)->countAllResults();
$funeralInterestsBefore = $db->tableExists('client_funeral_interests')
    ? (int) $db->table('client_funeral_interests')->where('client_id', $clientId)->countAllResults()
    : 0;

$login = httpRequest('POST', $baseUrl . '/login/auth', [
    'post' => http_build_query([
        'email'    => $user['email'],
        'password' => $tempPass,
    ]),
    'headers' => ['Content-Type: application/x-www-form-urlencoded'],
    'follow'  => false,
]);

if (! in_array($login['code'], [302, 303], true)) {
    fail('Login HTTP ' . $login['code'] . ' (esperado redirect 302/303)');
}
pass('Login respondió redirect (sesión iniciada)');

$view = httpRequest('GET', $baseUrl . '/prospects/view/' . $clientId);
if ($view['code'] !== 200) {
    fail('Vista prospecto HTTP ' . $view['code']);
}
if (strpos($view['body'], 'id="followUpForm"') === false) {
    fail('La vista no contiene el formulario unificado #followUpForm');
}
if (strpos($view['body'], 'Registrar seguimiento') === false) {
    fail('La vista no muestra el encabezado del formulario unificado');
}
pass('Vista prospecto carga formulario unificado');

$stamp = date('Y-m-d H:i:s');
$post = [
    'discussion_detail'       => 'Smoke test seguimiento unificado ' . $stamp,
    'contact_method'          => 'WhatsApp',
    'contact_datetime'        => date('Y-m-d\TH:i'),
    'personal_notes'          => 'Nota automática de prueba',
    'interest_level'          => 'Alto',
    'interest_notes'          => 'Interés registrado en prueba automática',
    'schedule_next_meeting'   => '1',
    'activity_type'           => 'Reunión',
    'activity_title'          => 'Reunión smoke test',
    'activity_start_time'     => date('Y-m-d H:i', strtotime('+2 days 10:00')),
    'activity_description'    => 'Cita creada por smoke test',
];

if ($property) {
    $post['real_estate_ids'] = [(int) $property['id']];
}
if ($funeral) {
    $post['funeral_product_ids'] = [(int) $funeral['id']];
    $post['payment_frequency'] = 'Mensual';
}

$save = httpRequest('POST', $baseUrl . '/prospects/followup/' . $clientId, [
    'post' => $post,
    'headers' => [
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json',
    ],
]);

if ($save['code'] !== 200) {
    fail('POST followup HTTP ' . $save['code'] . ' body=' . substr($save['body'], 0, 400));
}

$body = json_decode($save['body'], true);
if (! is_array($body) || empty($body['success'])) {
    fail('saveFollowUp no devolvió success. ' . substr($save['body'], 0, 500));
}

pass('POST seguimiento respondió success=true');

$checks = [
    'followUpHtml presente' => ! empty($body['followUpHtml']),
    'activityScheduled'     => ! empty($body['activityScheduled']),
];

if ($property) {
    $checks['propertiesAdded >= 1'] = ($body['propertiesAdded'] ?? 0) >= 1;
    $checks['newPropertyHtml']      = ! empty($body['newPropertyHtml']);
}
if ($funeral) {
    $checks['funeralsAdded >= 1'] = ($body['funeralsAdded'] ?? 0) >= 1;
    $checks['newFuneralHtml']     = ! empty($body['newFuneralHtml']);
}

foreach ($checks as $label => $ok) {
    if (! $ok) {
        fail("Respuesta JSON incompleta: {$label}");
    }
    pass($label);
}

$followUpsAfter = (int) $db->table('client_follow_ups')->where('client_id', $clientId)->countAllResults();
$activitiesAfter = (int) $db->table('calendar_activities')->where('client_id', $clientId)->countAllResults();
$propertyInterestsAfter = (int) $db->table('client_real_estate_interests')->where('client_id', $clientId)->countAllResults();
$funeralInterestsAfter = $db->tableExists('client_funeral_interests')
    ? (int) $db->table('client_funeral_interests')->where('client_id', $clientId)->countAllResults()
    : 0;

if ($followUpsAfter !== $followUpsBefore + 1) {
    fail("Minutas: esperado +1, antes={$followUpsBefore}, después={$followUpsAfter}");
}
pass('Minuta insertada en BD (+1)');

if ($activitiesAfter !== $activitiesBefore + 1) {
    fail("Actividades: esperado +1, antes={$activitiesBefore}, después={$activitiesAfter}");
}
pass('Cita agendada en BD (+1)');

if ($property) {
    if ($propertyInterestsAfter !== $propertyInterestsBefore + 1) {
        fail("Intereses inmueble: esperado +1, antes={$propertyInterestsBefore}, después={$propertyInterestsAfter}");
    }
    pass('Interés inmueble insertado (+1)');
}

if ($funeral) {
    if ($funeralInterestsAfter !== $funeralInterestsBefore + 1) {
        fail("Intereses funerario: esperado +1, antes={$funeralInterestsBefore}, después={$funeralInterestsAfter}");
    }
    pass('Interés funerario insertado (+1)');
}

@unlink($cookieJar);

echo "\nSmoke test completado OK.\n";
echo "Prospecto probado: id={$clientId}\n";
echo "Inmueble en prueba: " . ($property ? 'sí (id=' . (int) $property['id'] . ')' : 'no disponible') . "\n";
echo "Paquete funerario en prueba: " . ($funeral ? 'sí (id=' . (int) $funeral['id'] . ')' : 'no disponible') . "\n";
echo "URL: {$baseUrl}/prospects/view/{$clientId}\n";
