<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Borra datos operativos (prospectos, inmuebles, proyectos, CRM y catálogo funerario)
 * sin tocar usuarios, roles ni catálogos generales de configuración.
 */
class OperationalDataResetService
{
    /** @var list<string> */
    private array $tablesInOrder = [
        'client_document_checklist',
        'client_account_movements',
        'client_accounts',
        'client_funeral_interest_plans',
        'client_funeral_interests',
        'client_real_estate_interests',
        'client_follow_ups',
        'calendar_activities',
        'documents',
        'real_estate_photos',
        'real_estate_amenities',
        'real_estates',
        'real_estate_projects',
        'clients',
        'funeral_financing_plans',
        'funeral_products',
        'funeral_location_homes',
        'funeral_product_types',
        'funeral_homes',
        'funeral_locations',
    ];

    /** @var list<string> */
    private array $uploadDirectories = [
        'uploads/real_estate_photos',
        'uploads/documents',
        'uploads/client_documents',
    ];

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db = $this->db ?? db_connect();
    }

    /** @return list<string> Tablas que se vacían */
    public function getTargetTables(): array
    {
        return array_values(array_filter(
            $this->tablesInOrder,
            fn (string $table) => $this->db->tableExists($table)
        ));
    }

    /** @return array{tables: array<string, int>, files_deleted: int, upload_dirs: array<string, int>} */
    public function execute(): array
    {
        $tableCounts = [];

        $this->db->transStart();

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($this->getTargetTables() as $table) {
            $tableCounts[$table] = (int) $this->db->table($table)->countAllResults();
            $this->db->query('TRUNCATE TABLE `' . str_replace('`', '', $table) . '`');
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();

            throw new \RuntimeException('No se pudo completar el reinicio de datos en la base de datos.');
        }

        $this->db->transComplete();

        $uploadStats = $this->clearUploadDirectories();

        return [
            'tables'        => $tableCounts,
            'files_deleted' => array_sum($uploadStats),
            'upload_dirs'   => $uploadStats,
        ];
    }

    /** @return array<string, int> */
    private function clearUploadDirectories(): array
    {
        $stats = [];

        foreach ($this->uploadDirectories as $relativeDir) {
            $stats[$relativeDir] = $this->clearDirectory(FCPATH . $relativeDir);
        }

        return $stats;
    }

    private function clearDirectory(string $absoluteDir): int
    {
        if (! is_dir($absoluteDir)) {
            return 0;
        }

        $deleted = 0;
        $items   = scandir($absoluteDir) ?: [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.gitkeep') {
                continue;
            }

            $path = $absoluteDir . DIRECTORY_SEPARATOR . $item;

            if (is_file($path) && @unlink($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
