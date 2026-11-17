<?php

namespace App\Libraries;

/**
 * Compatibilidad cliente ↔ inmueble (0 a 100 puntos):
 *  - Zona (30): misma zona de interés.
 *  - Presupuesto (40): precio dentro del crédito/presupuesto del cliente.
 *  - Forma de pago (30): el inmueble acepta la forma de pago del cliente.
 * Si falta un dato del cliente se da puntaje parcial (no se descarta) y se indica qué completar.
 */
class Matching
{
    public const MINIMO = 50;

    private array $nombresPago = [];

    public function __construct()
    {
        try {
            foreach (db_connect()->table('payment_methods')->select('id, name')->get()->getResultArray() as $pm) {
                $this->nombresPago[(int) $pm['id']] = $pm['name'];
            }
        } catch (\Throwable $e) {
            // Sin catálogo: se usa el mapa histórico
        }
        $this->nombresPago += [1 => 'Infonavit', 2 => 'Fovissste', 3 => 'Contado', 4 => 'Crédito Bancario'];
    }

    /** Clientes compradores que aún no han comprado (excluye descartados/cancelados). */
    public function clientesPendientes(?int $asesorId = null): array
    {
        $db = db_connect();
        $q = $db->table('clients c')
            ->select('c.id, c.full_name, c.phone, c.email, c.status, c.credit_amount, c.zone_id, c.payment_method_id, c.user_id, c.is_funeral_prospect, c.created_at, z.name AS zone_name, u.full_name AS asesor_name', false)
            ->select('(SELECT MAX(contact_datetime) FROM client_follow_ups f WHERE f.client_id = c.id) AS ultimo_contacto', false)
            ->select('(SELECT COUNT(*) FROM client_real_estate_interests i WHERE i.client_id = c.id) AS intereses', false)
            ->join('zones z', 'z.id = c.zone_id', 'left')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->groupStart()->where('c.client_type', 'comprador')->orWhere('c.client_type IS NULL', null, false)->groupEnd()
            ->whereNotIn('c.status', ['Descartado', 'Cancelado', 'Cierre'])
            // Fuera los que ya compraron o ya apartaron (esos están en Trámites)
            ->where("NOT EXISTS (SELECT 1 FROM client_real_estate_interests v WHERE v.client_id = c.id AND v.status IN ('Vendido', 'Apartado'))", null, false)
            // Prospectos solo funerarios sin datos de inmueble no se consideran
            ->where('NOT (c.is_funeral_prospect = 1 AND COALESCE(c.credit_amount, 0) = 0 AND c.zone_id IS NULL AND c.payment_method_id IS NULL)', null, false)
            ->orderBy('c.created_at', 'DESC');

        if ($asesorId) {
            $q->where('c.user_id', $asesorId);
        }

        return $q->get()->getResultArray();
    }

    /** Inmuebles publicados (disponibles / apartados) con su foto principal. */
    public function inmueblesDisponibles(): array
    {
        // Solo libres: los apartados ya están reservados para otro cliente
        $lista = array_values(array_filter((new \App\Models\RealEstateModel())->getPublicCatalog(), static fn ($p) => in_array($p['status'], ['Disponible', 'Nuevo'], true)));
        $fotos = [];
        if ($lista) {
            $rows = db_connect()->table('real_estate_photos')->select('real_estate_id, MIN(id) AS id', false)
                ->whereIn('real_estate_id', array_column($lista, 'id'))->groupBy('real_estate_id')->get()->getResultArray();
            $ids = array_column($rows, 'id');
            if ($ids) {
                foreach (db_connect()->table('real_estate_photos')->select('real_estate_id, file_path')->whereIn('id', $ids)->get()->getResultArray() as $f) {
                    $fotos[(int) $f['real_estate_id']] = $f['file_path'];
                }
            }
        }
        foreach ($lista as &$p) {
            $p['main_photo_path'] = $fotos[(int) $p['id']] ?? null;
            $p['payment_methods_list'] = \App\Helpers\CatalogoHelper::parsePaymentMethods($p['payment_methods'] ?? null);
            $p['project_slug'] = \App\Helpers\CatalogoHelper::slug($p['project_name'] ?? 'general');
            $p['slug'] = \App\Helpers\CatalogoHelper::slug($p['name'] ?? 'inmueble');
        }

        return $lista;
    }

    /** Nombre de la forma de pago del cliente (o null). */
    public function formaPagoCliente(array $cliente): ?string
    {
        $id = (int) ($cliente['payment_method_id'] ?? 0);

        return $id ? ($this->nombresPago[$id] ?? null) : null;
    }

    /**
     * @return array{puntos:int, razones:list<array{ok:bool|null,texto:string}>, excluido:bool}
     */
    public function evaluar(array $cliente, array $inmueble): array
    {
        $puntos  = 0;
        $razones = [];
        $precio  = (float) $inmueble['price'];

        // Zona
        if (empty($cliente['zone_id'])) {
            $puntos += 10;
            $razones[] = ['ok' => null, 'texto' => 'El cliente no tiene zona de interés registrada'];
        } elseif ((int) $cliente['zone_id'] === (int) $inmueble['zone_id']) {
            $puntos += 30;
            $razones[] = ['ok' => true, 'texto' => 'En su zona de interés (' . ($inmueble['zone_name'] ?? '') . ')'];
        } else {
            $razones[] = ['ok' => false, 'texto' => 'Otra zona: ' . ($inmueble['zone_name'] ?: 'sin zona')];
        }

        // Presupuesto
        $credito = (float) ($cliente['credit_amount'] ?? 0);
        if ($credito <= 0) {
            $puntos += 15;
            $razones[] = ['ok' => null, 'texto' => 'El cliente no tiene presupuesto/crédito registrado'];
        } elseif ($precio <= 0) {
            $puntos += 20;
            $razones[] = ['ok' => null, 'texto' => 'Inmueble con precio a consultar'];
        } else {
            $ratio = $precio / $credito;
            if ($ratio <= 1.0) {
                // Muy por debajo del presupuesto (p. ej. un terreno para quien busca casa) suma menos
                $puntos += $ratio >= 0.6 ? 40 : ($ratio >= 0.4 ? 30 : 15);
                $razones[] = ['ok' => true, 'texto' => 'Dentro de su presupuesto (' . precio_corto($credito) . ')'];
            } elseif ($ratio <= 1.15) {
                $puntos += 25;
                $razones[] = ['ok' => null, 'texto' => 'Un ' . round(($ratio - 1) * 100) . '% arriba de su presupuesto'];
            } elseif ($ratio <= 1.3) {
                $puntos += 10;
                $razones[] = ['ok' => false, 'texto' => 'Un ' . round(($ratio - 1) * 100) . '% arriba de su presupuesto'];
            } else {
                return ['puntos' => 0, 'razones' => [['ok' => false, 'texto' => 'Muy por encima de su presupuesto']], 'excluido' => true];
            }
        }

        // Forma de pago
        $pagoCliente = $this->formaPagoCliente($cliente);
        $aceptadas   = array_values(array_filter($inmueble['payment_methods_list'] ?? [], static fn ($m) => self::norm($m) !== 'todos'));
        $aceptaTodo  = $aceptadas === [] || in_array('todos', array_map([self::class, 'norm'], $inmueble['payment_methods_list'] ?? []), true);
        if ($pagoCliente === null || self::norm($pagoCliente) === 'otro') {
            $puntos += 15;
            $razones[] = ['ok' => null, 'texto' => 'El cliente no tiene forma de pago registrada'];
        } elseif ($aceptaTodo || in_array(self::norm($pagoCliente), array_map([self::class, 'norm'], $aceptadas), true)) {
            $puntos += 30;
            $razones[] = ['ok' => true, 'texto' => 'Acepta ' . $pagoCliente];
        } else {
            $razones[] = ['ok' => false, 'texto' => 'No acepta ' . $pagoCliente . ' (acepta: ' . implode(', ', $aceptadas) . ')'];
        }

        return ['puntos' => min(100, $puntos), 'razones' => $razones, 'excluido' => false];
    }

    /** Inmuebles compatibles con un cliente, ordenados por puntaje. */
    public function paraCliente(array $cliente, array $inmuebles, int $minimo = self::MINIMO): array
    {
        $ya = array_column(db_connect()->table('client_real_estate_interests')->select('real_estate_id, status')
            ->where('client_id', $cliente['id'])->get()->getResultArray(), 'status', 'real_estate_id');

        $res = [];
        foreach ($inmuebles as $i) {
            $e = $this->evaluar($cliente, $i);
            if ($e['excluido'] || $e['puntos'] < $minimo || ($ya[$i['id']] ?? '') === 'Cancelado') {
                continue;
            }
            $res[] = $i + ['match' => $e, 'ya_interesado' => isset($ya[$i['id']])];
        }
        usort($res, static fn ($a, $b) => [$b['match']['puntos'], $a['price']] <=> [$a['match']['puntos'], $b['price']]);

        return $res;
    }

    /** Clientes pendientes compatibles con un inmueble. */
    public function paraInmueble(array $inmueble, ?int $asesorId = null, int $minimo = self::MINIMO): array
    {
        $inmueble['payment_methods_list'] ??= \App\Helpers\CatalogoHelper::parsePaymentMethods($inmueble['payment_methods'] ?? null);
        $res = [];
        foreach ($this->clientesPendientes($asesorId) as $c) {
            $e = $this->evaluar($c, $inmueble);
            if (! $e['excluido'] && $e['puntos'] >= $minimo) {
                $res[] = $c + ['match' => $e];
            }
        }
        usort($res, static fn ($a, $b) => $b['match']['puntos'] <=> $a['match']['puntos']);

        return $res;
    }

    public static function norm($s): string
    {
        $s = mb_strtolower(trim((string) $s));
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
        // "Crédito Bancario" (catálogo de clientes) == "Bancario" (inmuebles), aunque el acento venga mal codificado
        if (str_ends_with($s, 'bancario')) {
            return 'bancario';
        }

        return $s;
    }
}
