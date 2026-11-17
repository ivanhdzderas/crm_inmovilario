<?php

namespace App\Controllers;

use App\Libraries\Matching;

/** "¿Qué le ofrezco?": clientes pendientes de comprar y los inmuebles compatibles con cada uno. */
class MatchingController extends BaseController
{
    public function index(): string
    {
        $esAdmin = (int) session()->get('role_id') === 1;
        $asesor  = $esAdmin ? ((int) $this->request->getGet('asesor') ?: null) : (int) session()->get('user_id');

        $m         = new Matching();
        $inmuebles = $m->inmueblesDisponibles();
        $filas     = [];
        $resumen   = ['clientes' => 0, 'con_opciones' => 0, 'sin_datos' => 0];

        foreach ($m->clientesPendientes($asesor) as $c) {
            $opciones = $m->paraCliente($c, $inmuebles);
            $faltan   = array_keys(array_filter([
                'presupuesto'   => (float) $c['credit_amount'] <= 0,
                'zona'          => empty($c['zone_id']),
                'forma de pago' => $m->formaPagoCliente($c) === null,
            ]));
            $filas[] = $c + [
                'opciones'   => $opciones,
                'forma_pago' => $m->formaPagoCliente($c),
                'faltan'     => $faltan,
            ];
            $resumen['clientes']++;
            $resumen['con_opciones'] += $opciones ? 1 : 0;
            $resumen['sin_datos'] += count($faltan) >= 2 ? 1 : 0;
        }

        $orden = (string) $this->request->getGet('orden');
        usort($filas, match ($orden) {
            'contacto' => static fn ($a, $b) => strcmp((string) $a['ultimo_contacto'], (string) $b['ultimo_contacto']),
            'recientes' => static fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']),
            default => static fn ($a, $b) => [count($b['opciones']), $b['opciones'][0]['match']['puntos'] ?? 0] <=> [count($a['opciones']), $a['opciones'][0]['match']['puntos'] ?? 0],
        });

        return view('matching/index', [
            'filas'          => $filas,
            'resumen'        => $resumen,
            'totalInmuebles' => count($inmuebles),
            'esAdmin'        => $esAdmin,
            'asesores'       => $esAdmin ? db_connect()->table('users')->select('id, full_name')->where('is_active', 1)->orderBy('full_name')->get()->getResultArray() : [],
            'asesorSel'      => $asesor,
            'orden'          => $orden,
        ]);
    }

    public function cliente(int $id): string
    {
        $m       = new Matching();
        $cliente = db_connect()->table('clients c')
            ->select('c.*, z.name AS zone_name, u.full_name AS asesor_name')
            ->join('zones z', 'z.id = c.zone_id', 'left')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->where('c.id', $id)->get()->getRowArray();

        if (! $cliente || ! $this->puedeVer($cliente)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $minimo = $this->request->getGet('todos') ? 1 : Matching::MINIMO;

        return view('matching/cliente', [
            'cliente'   => $cliente,
            'formaPago' => $m->formaPagoCliente($cliente),
            'opciones'  => $m->paraCliente($cliente, $m->inmueblesDisponibles(), $minimo),
            'todos'     => $minimo === 1,
        ]);
    }

    /** JSON para el botón "Prospectos interesados" de cada inmueble. */
    public function inmueble(int $id)
    {
        $inmueble = (new \App\Models\RealEstateModel())->getPublicCatalogQuery()->where('real_estates.id', $id)->first();
        if (! $inmueble) {
            return $this->response->setJSON(['success' => false, 'message' => 'El inmueble no está disponible.']);
        }

        $asesor = (int) session()->get('role_id') === 1 ? null : (int) session()->get('user_id');
        $lista  = (new Matching())->paraInmueble($inmueble, $asesor);

        return $this->response->setJSON([
            'success'     => true,
            'real_estate' => ['clave' => $inmueble['clave'], 'name' => $inmueble['name'], 'price' => $inmueble['price'], 'zone_id' => $inmueble['zone_id']],
            'prospects'   => array_map(static fn ($c) => [
                'id' => $c['id'], 'full_name' => $c['full_name'], 'phone' => $c['phone'], 'email' => $c['email'],
                'credit_amount' => $c['credit_amount'], 'zone_name' => $c['zone_name'], 'asesor_name' => $c['asesor_name'],
                'score' => $c['match']['puntos'],
                'reasons' => array_column($c['match']['razones'], 'texto'),
            ], $lista),
            'total'       => count($lista),
        ]);
    }

    private function puedeVer(array $cliente): bool
    {
        return (int) session()->get('role_id') === 1 || (int) $cliente['user_id'] === (int) session()->get('user_id');
    }
}
