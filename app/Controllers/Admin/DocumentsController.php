<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DocumentTypeModel;
use App\Models\RealEstateModel;
use App\Models\DocumentsModel;
use App\Models\ConfigurationModel; // <-- La clase correcta ahora

class DocumentsController extends BaseController
{
    /**
     * Muestra la lista de documentos para un inmueble específico y los documentos ya subidos.
     *
     * @param int $id El ID del inmueble.
     * @return string
     */
    public function index(int $id): string
    {
        $realEstateModel = new RealEstateModel();
        $documentTypeModel = new DocumentTypeModel();
        $documentsModel = new DocumentsModel();
        $configModel = new ConfigurationModel(); // Instanciamos el modelo de configuración

        $data['realEstate'] = $realEstateModel->find($id);

        if (empty($data['realEstate'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se puede encontrar el inmueble con el ID: ' . $id);
        }

        // Obtenemos la configuración actual para los requisitos de documentos
        $config = $configModel->where('config_key', 'document_requirement_type')->first();
        $requiredType = $config['config_value'] ?? 'all'; // Valor predeterminado a 'all' si no existe

        // Obtenemos todos los tipos de documentos
        $allDocumentTypes = $documentTypeModel->findAll();

        $filteredDocumentTypes = [];
        $isNew = (bool) $data['realEstate']['is_new'];

        // Filtramos los tipos de documentos según la configuración
        if ($requiredType === 'all') {
            $filteredDocumentTypes = $allDocumentTypes;
        } elseif ($requiredType === 'new' && $isNew) {
            $filteredDocumentTypes = $allDocumentTypes;
        } elseif ($requiredType === 'used' && !$isNew) {
            $filteredDocumentTypes = $allDocumentTypes;
        }

        $data['documentTypes'] = $filteredDocumentTypes;

        // Obtenemos los documentos que ya han sido subidos para este inmueble
        $data['uploadedDocuments'] = $documentsModel
            ->select('documents.*, document_types.name as document_name')
            ->join('document_types', 'document_types.id = documents.document_type_id')
            ->where('documents.real_estate_id', $id)
            ->findAll();

        return view('admin/real_estates/documents', $data);
    }

    /**
     * Procesa la subida de un documento para un inmueble.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function upload()
    {
        $validationRules = [
            'document_file' => [
                'rules' => 'uploaded[document_file]|max_size[document_file,10240]|ext_in[document_file,jpg,jpeg,png,pdf,docx]',
                'errors' => [
                    'uploaded' => 'Por favor, selecciona un archivo.',
                    'max_size' => 'El archivo no puede exceder los 10MB.',
                    'ext_in'   => 'Solo se permiten archivos JPG, JPEG, PNG, PDF y DOCX.',
                ],
            ],
            'real_estate_id' => 'required|numeric',
            'document_type_id' => 'required|numeric',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $realEstateId = $this->request->getPost('real_estate_id');
        $documentTypeId = $this->request->getPost('document_type_id');
        $file = $this->request->getFile('document_file');
        
        $documentsModel = new DocumentsModel();

        // Verificamos si ya existe un documento de este tipo para el inmueble
        $existingDocument = $documentsModel
            ->where('real_estate_id', $realEstateId)
            ->where('document_type_id', $documentTypeId)
            ->first();

        // Si el archivo se sube correctamente, lo guardamos
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $path = 'uploads/documents/' . $newName;

            $file->move(ROOTPATH . 'public/uploads/documents', $newName);
            
            $data = [
                'real_estate_id'   => $realEstateId,
                'document_type_id' => $documentTypeId,
                'file_path'        => $path,
                'is_uploaded'      => 1,
            ];

            if ($existingDocument) {
                // Si ya existe, actualizamos el registro y eliminamos el archivo anterior
                $documentsModel->update($existingDocument['id'], $data);
                if (file_exists(ROOTPATH . 'public/' . $existingDocument['file_path'])) {
                    unlink(ROOTPATH . 'public/' . $existingDocument['file_path']);
                }
                $message = 'Documento actualizado correctamente.';
            } else {
                // Si no existe, creamos un nuevo registro
                $documentsModel->insert($data);
                $message = 'Documento subido correctamente.';
            }
            return redirect()->to('admin/real_estates/documents/' . $realEstateId)->with('success', $message);
        }

        return redirect()->back()->with('error', 'Hubo un error al subir el archivo.');
    }

    /**
     * Elimina un documento de la base de datos y del sistema de archivos.
     *
     * @param int $id El ID del documento a eliminar.
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete(int $id)
    {
        $documentsModel = new DocumentsModel();
        $document = $documentsModel->find($id);

        if (!$document) {
            return redirect()->back()->with('error', 'El documento no fue encontrado.');
        }

        // Obtener el ID del inmueble para la redirección
        $realEstateId = $document['real_estate_id'];

        // Eliminar el archivo del sistema de archivos
        $filePath = ROOTPATH . 'public/' . $document['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Eliminar el registro de la base de datos
        $documentsModel->delete($id);

        return redirect()->to('admin/real_estates/documents/' . $realEstateId)->with('success', 'Documento eliminado correctamente.');
    }
}
