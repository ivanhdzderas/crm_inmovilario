<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClientDocumentCatalogModel;

class ClientDocumentCatalogController extends BaseController
{
    protected $catalogModel;
    protected array $allowedIconExtensions = ['png', 'jpg', 'jpeg', 'webp'];

    public function __construct()
    {
        $this->catalogModel = new ClientDocumentCatalogModel();
    }

    /**
     * Muestra el catálogo principal de tipos de crédito
     */
    public function index(): string
    {
        // Obtener tipos de crédito dinámicamente desde la base de datos
        $creditTypes = $this->catalogModel->getCreditTypes();
        foreach ($creditTypes as &$creditType) {
            $creditType['icon_url'] = $this->findCreditTypeIconUrl($creditType['name']);
        }
        unset($creditType);
        
        $data['creditTypes'] = $creditTypes;
        
        return view('admin/client_document_catalog/index', $data);
    }

    /**
     * Gestiona documentos para un tipo de crédito específico
     */
    public function manageCreditType($creditType = null): string
    {
        if (!$creditType) {
            return redirect()->to(base_url('admin/client_document_catalog'));
        }

        $creditType = urldecode($creditType);
        
        // Obtener documentos para este tipo de crédito
        $documents = $this->catalogModel->getDocumentsByCreditType($creditType);
        
        $data['creditType'] = $creditType;
        $data['creditTypeIconUrl'] = $this->findCreditTypeIconUrl($creditType);
        $data['documents'] = $documents;
        $data['totalDocuments'] = count($documents);
        
        return view('admin/client_document_catalog/manage_credit_type', $data);
    }

    /**
     * Agrega un nuevo tipo de crédito
     */
    public function addCreditType()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $creditTypeName = trim($this->request->getPost('credit_type_name'));
        
        if (empty($creditTypeName)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El nombre del tipo de crédito es requerido'
            ]);
        }

        $iconFile = $this->request->getFile('credit_type_icon');
        $hasIcon = $iconFile && $iconFile->getName() !== '';
        if ($hasIcon) {
            if (!$iconFile->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El archivo de icono no es válido'
                ]);
            }

            $ext = strtolower($iconFile->getClientExtension() ?: $iconFile->getExtension());
            if (!in_array($ext, $this->allowedIconExtensions, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Formato de icono no permitido. Usa PNG, JPG, JPEG o WEBP.'
                ]);
            }
        }

        // Verificar si ya existe
        if ($this->catalogModel->creditTypeExists($creditTypeName)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este tipo de crédito ya existe'
            ]);
        }

        // Crear el nuevo tipo de crédito
        $result = $this->catalogModel->createCreditType($creditTypeName);
        
        if ($result) {
            if ($hasIcon) {
                try {
                    $this->saveCreditTypeIcon($creditTypeName, $iconFile);
                } catch (\Throwable $e) {
                    log_message('error', 'No se pudo guardar icono de tipo de crédito: ' . $e->getMessage());
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Tipo de crédito creado, pero no se pudo guardar el icono. Intenta nuevamente.',
                        'credit_type' => $creditTypeName
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tipo de crédito agregado correctamente',
                'credit_type' => $creditTypeName
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al crear el tipo de crédito'
            ]);
        }
    }

    /**
     * Elimina un tipo de crédito y todos sus documentos
     */
    public function deleteCreditType()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $creditType = $this->request->getPost('credit_type');
        
        if (empty($creditType)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tipo de crédito no especificado'
            ]);
        }

        // Eliminar todos los documentos de este tipo de crédito
        $result = $this->catalogModel->deleteCreditTypeAndDocuments($creditType);
        
        if ($result) {
            $this->removeCreditTypeIcons($creditType);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tipo de crédito y sus documentos eliminados correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el tipo de crédito'
            ]);
        }
    }

    /**
     * Agrega un documento a un tipo de crédito específico
     */
    public function addDocument()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $rules = [
            'credit_type' => 'required',
            'document_name' => 'required|max_length[255]',
            'description' => 'permit_empty',
            'category' => 'required|max_length[100]',
            'document_type' => 'required|in_list[Obligatorio,Opcional,Condicional]',
            'process_stage' => 'required|in_list[Apartado,Escrituración,Entrega,Post-venta]',
            'requires_notary' => 'permit_empty',
            'cost_estimate' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('document_name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'document_type' => $this->request->getPost('document_type'),
            'process_stage' => $this->request->getPost('process_stage'),
            'payment_method' => $this->request->getPost('credit_type'),
            'requires_notary' => $this->request->getPost('requires_notary') ? 1 : 0,
            'cost_estimate' => $this->request->getPost('cost_estimate') ?: 0,
            'is_active' => 1,
            'sort_order' => $this->catalogModel->getNextSortOrder($this->request->getPost('credit_type'))
        ];

        if ($this->catalogModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento agregado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al agregar el documento'
            ]);
        }
    }

    /**
     * Elimina un documento específico
     */
    public function deleteDocument($documentId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        if ($this->catalogModel->delete($documentId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el documento'
            ]);
        }
    }

    /**
     * Obtiene los datos de un documento específico para edición
     */
    public function getDocument($documentId)
    {
        log_message('info', "getDocument called with ID: {$documentId}"); // Debug
        
        if (!$this->request->isAJAX()) {
            log_message('info', "Not AJAX request"); // Debug
            return redirect()->back();
        }

        $document = $this->catalogModel->find($documentId);
        
        log_message('info', "Document found: " . json_encode($document)); // Debug
        
        if (!$document) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Documento no encontrado'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'document' => $document
        ]);
    }

    /**
     * Actualiza un documento específico
     */
    public function updateDocument($documentId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $rules = [
            'document_name' => 'required|max_length[255]',
            'description' => 'permit_empty',
            'category' => 'required|max_length[100]',
            'document_type' => 'required|in_list[Obligatorio,Opcional,Condicional]',
            'process_stage' => 'required|in_list[Apartado,Escrituración,Entrega,Post-venta]',
            'requires_notary' => 'permit_empty',
            'cost_estimate' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('document_name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'document_type' => $this->request->getPost('document_type'),
            'process_stage' => $this->request->getPost('process_stage'),
            'requires_notary' => $this->request->getPost('requires_notary') ? 1 : 0,
            'cost_estimate' => $this->request->getPost('cost_estimate') ?: 0
        ];

        if ($this->catalogModel->update($documentId, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento actualizado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el documento'
            ]);
        }
    }

    /**
     * Actualiza el orden de los documentos
     */
    public function updateOrder()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $documentIds = $this->request->getPost('document_ids');
        
        if (empty($documentIds) || !is_array($documentIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos de ordenamiento inválidos'
            ]);
        }

        $result = $this->catalogModel->updateDocumentOrder($documentIds);
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Orden actualizado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el orden'
            ]);
        }
    }

    private function creditTypeIconDirectory(): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'icons';
    }

    private function normalizeCreditTypeFileBase(string $creditTypeName): string
    {
        $normalized = strtolower(trim($creditTypeName));
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        if ($trans !== false) {
            $normalized = $trans;
        }

        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
        $normalized = trim((string) $normalized, '_');

        return $normalized !== '' ? $normalized : 'credito';
    }

    private function removeCreditTypeIcons(string $creditTypeName): void
    {
        $baseName = $this->normalizeCreditTypeFileBase($creditTypeName);
        $dir = $this->creditTypeIconDirectory();

        foreach ($this->allowedIconExtensions as $ext) {
            $fullPath = $dir . DIRECTORY_SEPARATOR . $baseName . '.' . $ext;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    private function saveCreditTypeIcon(string $creditTypeName, \CodeIgniter\HTTP\Files\UploadedFile $iconFile): void
    {
        $dir = $this->creditTypeIconDirectory();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('No se pudo crear el directorio de iconos');
        }

        $ext = strtolower($iconFile->getClientExtension() ?: $iconFile->getExtension());
        if (!in_array($ext, $this->allowedIconExtensions, true)) {
            throw new \RuntimeException('Extensión de icono no permitida');
        }

        $this->removeCreditTypeIcons($creditTypeName);

        $baseName = $this->normalizeCreditTypeFileBase($creditTypeName);
        $fileName = $baseName . '.' . $ext;
        $iconFile->move($dir, $fileName, true);
    }

    private function findCreditTypeIconUrl(string $creditTypeName): ?string
    {
        $baseName = $this->normalizeCreditTypeFileBase($creditTypeName);
        $dir = $this->creditTypeIconDirectory();

        foreach ($this->allowedIconExtensions as $ext) {
            $fullPath = $dir . DIRECTORY_SEPARATOR . $baseName . '.' . $ext;
            if (is_file($fullPath)) {
                return base_url('icons/' . $baseName . '.' . $ext);
            }
        }

        return null;
    }
}