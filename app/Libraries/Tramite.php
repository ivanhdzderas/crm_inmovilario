<?php

namespace App\Libraries;

/** Etapas del trámite de compraventa de un inmueble y consultas de avance. */
class Tramite
{
    /** clave => [nombre, descripción corta] (en orden) */
    public const ETAPAS = [
        'apartado'   => ['Apartado', 'Se recibió el apartado del inmueble.'],
        'expediente' => ['Integración de expediente', 'Reunir los documentos del comprador y del inmueble.'],
        'credito'    => ['Autorización de crédito', 'Trámite y aprobación del crédito (Infonavit, Fovissste o banco).'],
        'avaluo'     => ['Avalúo', 'Avalúo del inmueble y dictamen.'],
        'firma'      => ['Firma en notaría', 'Firma de escrituras ante notario.'],
        'entregado'  => ['Entregado', 'Entrega del inmueble. Venta concluida.'],
    ];

    public static function nombre(?string $etapa): string
    {
        return self::ETAPAS[$etapa ?? 'apartado'][0] ?? 'Apartado';
    }

    public static function indice(?string $etapa): int
    {
        $i = array_search($etapa ?? 'apartado', array_keys(self::ETAPAS), true);

        return $i === false ? 0 : (int) $i;
    }

    public static function porcentaje(?string $etapa): int
    {
        return (int) round(self::indice($etapa) * 100 / (count(self::ETAPAS) - 1));
    }

    /** Trámites (intereses apartados o vendidos) con datos de cliente, inmueble, documentos y saldo. */
    public static function lista(?int $asesorId = null, bool $incluirConcluidos = false, ?int $clienteId = null, ?int $interesId = null): array
    {
        $db = db_connect();
        $q = $db->table('client_real_estate_interests i')
            ->select('i.id, i.client_id, i.real_estate_id, i.status, i.apartado_date, i.apartado_amount, i.tramite_etapa, i.tramite_actualizado, i.created_at', false)
            ->select('c.full_name, c.phone, c.email, c.user_id, u.full_name AS asesor_name, re.name AS inmueble, re.clave, re.price, re.status AS inmueble_status', false)
            ->select('ca.id AS cuenta_id, ca.current_balance, ca.status AS cuenta_status', false)
            ->select("(SELECT COUNT(*) FROM client_document_checklist d WHERE d.client_account_id = ca.id AND d.status <> 'No Aplica') AS docs_total", false)
            ->select("(SELECT COUNT(*) FROM client_document_checklist d WHERE d.client_account_id = ca.id AND d.status = 'Completado') AS docs_ok", false)
            ->select("(SELECT COUNT(*) FROM client_document_checklist d WHERE d.client_account_id = ca.id AND (d.status = 'Vencido' OR (d.status IN ('Pendiente','En Proceso') AND d.due_date < CURDATE()))) AS docs_vencidos", false)
            ->join('clients c', 'c.id = i.client_id')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->join('real_estates re', 're.id = i.real_estate_id', 'left')
            ->join('client_accounts ca', 'ca.real_estate_interest_id = i.id AND ca.client_id = i.client_id', 'left')
            ->whereIn('i.status', $incluirConcluidos ? ['Apartado', 'Vendido'] : ['Apartado'])
            ->orderBy('i.tramite_actualizado', 'ASC');

        if ($asesorId) {
            $q->where('c.user_id', $asesorId);
        }
        if ($clienteId) {
            $q->where('i.client_id', $clienteId);
        }
        if ($interesId) {
            $q->where('i.id', $interesId);
        }

        $filas = $q->get()->getResultArray();
        foreach ($filas as &$f) {
            $f['tramite_etapa'] = $f['tramite_etapa'] ?: ($f['status'] === 'Vendido' ? 'entregado' : 'apartado');
            $desde = $f['tramite_actualizado'] ?: ($f['apartado_date'] ?: $f['created_at']);
            $f['dias_en_etapa'] = $desde ? max(0, (int) floor((time() - strtotime($desde)) / 86400)) : null;
            $f['docs_pct'] = (int) $f['docs_total'] > 0 ? (int) round($f['docs_ok'] * 100 / $f['docs_total']) : null;
        }

        return $filas;
    }

    public static function historial(int $interesId): array
    {
        return db_connect()->table('tramite_historial h')
            ->select('h.*, u.full_name AS usuario')
            ->join('users u', 'u.id = h.user_id', 'left')
            ->where('h.interest_id', $interesId)
            ->orderBy('h.created_at', 'DESC')->orderBy('h.id', 'DESC')
            ->get()->getResultArray();
    }
}
