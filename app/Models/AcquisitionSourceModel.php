<?php

namespace App\Models;

use CodeIgniter\Model;

class AcquisitionSourceModel extends Model
{
    public const CHANNEL_LABELS = [
        'social'   => 'Redes sociales',
        'web'      => 'Web / Internet',
        'referral' => 'Referido',
        'offline'  => 'Presencial / Evento',
        'other'    => 'Otro',
    ];

    protected $table         = 'acquisition_sources';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'channel_type', 'icon_class', 'is_active', 'sort_order'];
    protected $useTimestamps = true;

    public function getActiveOrdered(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll() ?: [];
    }

    public function getGroupedForSelect(): array
    {
        $grouped = [];
        foreach ($this->getActiveOrdered() as $row) {
            $grouped[$row['channel_type']][] = $row;
        }

        return $grouped;
    }

    /**
     * Estadísticas por canal de adquisición.
     */
    public function getReportStats(?string $startDate = null, ?string $endDate = null, $userId = null): array
    {
        if (! $this->db->tableExists('acquisition_sources')) {
            return ['by_source' => [], 'by_channel' => [], 'totals' => ['prospects' => 0, 'clients' => 0]];
        }

        $builder = $this->db->table('clients c')
            ->select('
                acs.id,
                acs.name as source_name,
                acs.channel_type,
                acs.icon_class,
                COUNT(c.id) as total_prospects,
                SUM(CASE WHEN c.status = "Cliente" THEN 1 ELSE 0 END) as total_clients
            ')
            ->join('acquisition_sources acs', 'acs.id = c.acquisition_source_id', 'inner')
            ->groupBy('acs.id, acs.name, acs.channel_type, acs.icon_class')
            ->orderBy('total_prospects', 'DESC');

        if ($startDate) {
            $builder->where('DATE(c.created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(c.created_at) <=', $endDate);
        }
        if ($userId && $userId !== 'all') {
            $builder->where('c.user_id', (int) $userId);
        }

        $bySource = $builder->get()->getResultArray();

        $channelBuilder = $this->db->table('clients c')
            ->select('acs.channel_type, COUNT(c.id) as total_prospects, SUM(CASE WHEN c.status = "Cliente" THEN 1 ELSE 0 END) as total_clients')
            ->join('acquisition_sources acs', 'acs.id = c.acquisition_source_id', 'inner')
            ->groupBy('acs.channel_type')
            ->orderBy('total_prospects', 'DESC');

        if ($startDate) {
            $channelBuilder->where('DATE(c.created_at) >=', $startDate);
        }
        if ($endDate) {
            $channelBuilder->where('DATE(c.created_at) <=', $endDate);
        }
        if ($userId && $userId !== 'all') {
            $channelBuilder->where('c.user_id', (int) $userId);
        }

        $byChannel = $channelBuilder->get()->getResultArray();

        $withoutSource = $this->db->table('clients c')
            ->select('COUNT(c.id) as total')
            ->where('c.acquisition_source_id IS NULL', null, false);
        if ($startDate) {
            $withoutSource->where('DATE(c.created_at) >=', $startDate);
        }
        if ($endDate) {
            $withoutSource->where('DATE(c.created_at) <=', $endDate);
        }
        if ($userId && $userId !== 'all') {
            $withoutSource->where('c.user_id', (int) $userId);
        }
        $missing = (int) ($withoutSource->get()->getRowArray()['total'] ?? 0);

        $totalProspects = array_sum(array_column($bySource, 'total_prospects')) + $missing;
        $totalClients   = array_sum(array_column($bySource, 'total_clients'));

        return [
            'by_source'  => $bySource,
            'by_channel' => $byChannel,
            'totals'     => [
                'prospects'       => $totalProspects,
                'clients'         => $totalClients,
                'without_source'  => $missing,
            ],
        ];
    }
}
