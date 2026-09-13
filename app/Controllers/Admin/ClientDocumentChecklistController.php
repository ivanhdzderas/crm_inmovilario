<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClientDocumentChecklistModel;
use App\Models\ClientAccountModel;
use App\Models\ClientModel;

class ClientDocumentChecklistController extends BaseController
{
    protected $checklistModel;
    protected $accountModel;
    protected $clientModel;

    public function __construct()
    {
        $this->checklistModel = new ClientDocumentChecklistModel();
        $this->accountModel = new ClientAccountModel();
        $this->clientModel = new ClientModel();
    }

    /**
     * Muestra el checklist de documentos para una cuenta de cliente
     */
    public function view($accountId): string
    {
        $account = $this->accountModel->getAccountWithDetails($accountId);
        
        if (!$account) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cuenta no encontrada');
        }

        $data['account'] = $account;
        $data['documentsOrganized'] = $this->checklistModel->getAccountChecklistOrganized($accountId);
        $data['stats'] = $this->checklistModel->getChecklistStats($accountId);
        $data['paymentMethodSummary'] = $this->checklistModel->getPaymentMethodSummary($accountId);

        return view('admin/client_document_checklist/view_organized', $data);
    }

    /**
     * Actualiza el estado de un documento del checklist
     */
    public function updateStatus()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Solicitud inválida');
        }

        $checklistId = $this->request->getPost('checklist_id');
        $status = $this->request->getPost('status');
        $notes = $this->request->getPost('notes');
        $actualCost = $this->request->getPost('actual_cost');

        $rules = [
            'checklist_id' => 'required|integer',
            'status' => 'required|in_list[Pendiente,En Proceso,Completado,No Aplica,Vencido]',
            'notes' => 'permit_empty|max_length[1000]',
            'actual_cost' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $updateData = ['notes' => $notes];
        if ($actualCost !== null && $actualCost !== '') {
            $updateData['actual_cost'] = $actualCost;
        }

        if ($this->checklistModel->updateDocumentStatus($checklistId, $status, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Estado del documento actualizado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el estado del documento'
            ]);
        }
    }

    /**
     * Sube archivo para un documento del checklist
     */
    public function uploadFile()
    {
        $checklistId = $this->request->getPost('checklist_id');
        $file = $this->request->getFile('document_file');

        $validationRules = [
            'checklist_id' => 'required|integer',
            'document_file' => [
                'rules' => 'uploaded[document_file]|max_size[document_file,10240]|ext_in[document_file,jpg,jpeg,png,pdf,docx]',
                'errors' => [
                    'uploaded' => 'Por favor, selecciona un archivo.',
                    'max_size' => 'El archivo no puede exceder los 10MB.',
                    'ext_in' => 'Solo se permiten archivos JPG, JPEG, PNG, PDF y DOCX.',
                ],
            ],
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $checklistItem = $this->checklistModel->find($checklistId);
        if (!$checklistItem) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $path = 'uploads/client_documents/' . $newName;

            // Crear directorio si no existe
            $uploadPath = ROOTPATH . 'public/uploads/client_documents/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $newName);

            // Eliminar archivo anterior si existe
            if ($checklistItem['file_path'] && file_exists(ROOTPATH . 'public/' . $checklistItem['file_path'])) {
                unlink(ROOTPATH . 'public/' . $checklistItem['file_path']);
            }

            // Actualizar registro
            $updateData = [
                'file_path' => $path,
                'status' => 'Completado',
                'completed_date' => date('Y-m-d H:i:s')
            ];

            if ($this->checklistModel->update($checklistId, $updateData)) {
                return redirect()->back()->with('success', 'Archivo subido correctamente');
            }
        }

        return redirect()->back()->with('error', 'Error al subir el archivo');
    }

    /**
     * API: Obtiene checklist de una cuenta (para AJAX)
     */
    public function apiGetChecklist($accountId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                                 ->setJSON(['error' => 'Solo peticiones AJAX']);
        }

        $organized = $this->checklistModel->getAccountChecklistOrganized($accountId);
        $stats = $this->checklistModel->getChecklistStats($accountId);
        $summary = $this->checklistModel->getPaymentMethodSummary($accountId);

        return $this->response->setJSON([
            'success' => true,
            'documents_organized' => $organized,
            'stats' => $stats,
            'payment_method_summary' => $summary
        ]);
    }
}