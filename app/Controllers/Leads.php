<?php

namespace App\Controllers;

/**
 * Formulario "Me interesa" del sitio público: registra al visitante como prospecto
 * en el CRM (clients) y lo liga al inmueble o paquete funerario que estaba viendo.
 */
class Leads extends BaseController
{
    public function guardar()
    {
        $volver = $this->volverA();

        // Campo trampa para bots (oculto con CSS)
        if (trim((string) $this->request->getPost('sitio_web')) !== '') {
            return redirect()->to($volver . '#interes')->with('lead_ok', true);
        }

        if (service('throttler')->check('lead-' . md5((string) $this->request->getIPAddress()), 5, MINUTE) === false) {
            return redirect()->to($volver . '#interes')->withInput()->with('lead_error', 'Recibimos varios mensajes seguidos. Espera un minuto e inténtalo de nuevo.');
        }

        $reglas = [
            'nombre'   => 'required|min_length[3]|max_length[120]',
            'telefono' => 'permit_empty|regex_match[/^[\d\s()+\-]{10,20}$/]',
            'email'    => 'permit_empty|valid_email|max_length[150]',
            'mensaje'  => 'permit_empty|max_length[1000]',
            'tipo'     => 'required|in_list[inmueble,funerario,general]',
            'ref'      => 'permit_empty|is_natural_no_zero',
        ];
        $mensajes = [
            'nombre'   => ['required' => 'Escribe tu nombre.', 'min_length' => 'Escribe tu nombre completo.'],
            'telefono' => ['regex_match' => 'Escribe un teléfono válido de 10 dígitos.'],
            'email'    => ['valid_email' => 'Escribe un correo válido.'],
        ];
        if (! $this->validate($reglas, $mensajes)) {
            return redirect()->to($volver . '#interes')->withInput()->with('lead_error', implode(' ', $this->validator->getErrors()));
        }

        $d        = $this->validator->getValidated();
        $telefono = preg_replace('/\D/', '', (string) ($d['telefono'] ?? ''));
        $email    = strtolower(trim((string) ($d['email'] ?? '')));
        if ($telefono === '' && $email === '') {
            return redirect()->to($volver . '#interes')->withInput()->with('lead_error', 'Déjanos un teléfono o un correo para poder contactarte.');
        }

        $db   = db_connect();
        $tipo = $d['tipo'];
        $ref  = (int) ($d['ref'] ?? 0);

        // Qué estaba viendo (solo si está publicado)
        $objeto = null;
        if ($tipo === 'inmueble' && $ref) {
            $objeto = (new \App\Models\RealEstateModel())->getPublicCatalogQuery()->where('real_estates.id', $ref)->first();
        } elseif ($tipo === 'funerario' && $ref) {
            $objeto = $db->table('funeral_products')->select('id, clave, name')->where('id', $ref)->where('status', 'Disponible')->get()->getRowArray();
        }
        $detalle = 'Sitio web' . ($objeto ? ': ' . trim(($objeto['clave'] ?? '') . ' ' . $objeto['name']) : '');

        $db->transStart();

        // Si ya existe un prospecto con ese teléfono o correo, se reutiliza
        $cliente = null;
        if ($telefono !== '' || $email !== '') {
            $q = $db->table('clients')->select('id, user_id, is_funeral_prospect')->groupStart();
            if ($telefono !== '') {
                $q->where("REGEXP_REPLACE(phone, '[^0-9]', '') = " . $db->escape($telefono), null, false);
            }
            if ($email !== '') {
                $telefono !== '' ? $q->orWhere('email', $email) : $q->where('email', $email);
            }
            $cliente = $q->groupEnd()->orderBy('id', 'DESC')->get()->getRowArray();
        }

        $ahora = date('Y-m-d H:i:s');
        if ($cliente) {
            $clienteId = (int) $cliente['id'];
            if ($tipo === 'funerario' && ! (int) $cliente['is_funeral_prospect']) {
                $db->table('clients')->where('id', $clienteId)->update(['is_funeral_prospect' => 1, 'updated_at' => $ahora]);
            }
        } else {
            $db->table('clients')->insert([
                'user_id'               => $this->asesorParaProspectos(),
                'full_name'             => mb_convert_case(trim($d['nombre']), MB_CASE_TITLE, 'UTF-8'),
                'client_type'           => 'comprador',
                'is_funeral_prospect'   => $tipo === 'funerario' ? 1 : 0,
                'email'                 => $email !== '' ? $email : null,
                'phone'                 => $telefono !== '' ? $telefono : null,
                'status'                => 'Nuevo',
                'acquisition_source_id' => $this->origenSitioWeb(),
                'acquisition_detail'    => mb_substr($detalle, 0, 255),
                'created_at'            => $ahora,
                'updated_at'            => $ahora,
            ]);
            $clienteId = (int) $db->insertID();
        }

        $nota = trim('Desde el sitio web.' . (! empty($d['mensaje']) ? ' Mensaje: ' . $d['mensaje'] : ''));
        if ($objeto && $tipo === 'inmueble') {
            $existe = $db->table('client_real_estate_interests')->where('client_id', $clienteId)->where('real_estate_id', $objeto['id'])->countAllResults();
            if (! $existe) {
                $db->table('client_real_estate_interests')->insert([
                    'client_id' => $clienteId, 'real_estate_id' => $objeto['id'], 'notes' => $nota, 'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            }
        } elseif ($objeto && $tipo === 'funerario') {
            $existe = $db->table('client_funeral_interests')->where('client_id', $clienteId)->where('funeral_product_id', $objeto['id'])->countAllResults();
            if (! $existe) {
                $db->table('client_funeral_interests')->insert([
                    'client_id' => $clienteId, 'funeral_product_id' => $objeto['id'], 'notes' => $nota, 'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            }
        } elseif (! empty($d['mensaje']) && ! $cliente) {
            $db->table('clients')->where('id', $clienteId)->update(['acquisition_detail' => mb_substr($detalle . ' — ' . $d['mensaje'], 0, 255)]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', '[Leads] No se pudo guardar el prospecto del sitio.');

            return redirect()->to($volver . '#interes')->withInput()->with('lead_error', 'No pudimos enviar tu mensaje. Inténtalo de nuevo en unos minutos.');
        }

        return redirect()->to($volver . '#interes')->with('lead_ok', true);
    }

    /** Regresa a la página desde donde se envió (solo rutas internas). */
    private function volverA(): string
    {
        $ruta = (string) $this->request->getPost('volver');
        if ($ruta === '' || ! str_starts_with($ruta, '/') || str_starts_with($ruta, '//') || str_contains($ruta, "\n")) {
            $ruta = '/';
        }

        return rtrim(base_url(), '/') . $ruta;
    }

    /** Asesor configurado en Configuración > Sitio público; si no, el primer administrador activo. */
    private function asesorParaProspectos(): ?int
    {
        $db = db_connect();
        $id = (int) sitio('site_leads_user_id', 0);
        if ($id && $db->table('users')->where('id', $id)->where('is_active', 1)->countAllResults()) {
            return $id;
        }
        $admin = $db->table('users')->select('id')->where('role_id', 1)->where('is_active', 1)->orderBy('id')->get()->getRowArray();

        return $admin ? (int) $admin['id'] : null;
    }

    private function origenSitioWeb(): ?int
    {
        $fila = db_connect()->table('acquisition_sources')->select('id')->where('channel_type', 'web')->like('name', 'gina web')->get()->getRowArray();

        return $fila ? (int) $fila['id'] : null;
    }
}
