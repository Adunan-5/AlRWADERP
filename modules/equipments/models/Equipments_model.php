<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipments_model extends App_Model
{
    protected $table = 'tblequipments';

    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if ($id != '') {
            $this->db->where('id', $id);
            return $this->db->get($this->table)->row();
        }
        return $this->db->get($this->table)->result_array();
    }

    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        // Return true if query succeeded, even if no rows affected
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    /**
     * Get equipment document types from master table
     */
    public function get_equipment_document_types()
    {
        $this->db->where('active', 1);
        $this->db->order_by('display_order', 'ASC');
        return $this->db->get(db_prefix() . 'equipment_document_types')->result_array();
    }

    /**
     * Get equipment documents for a specific equipment
     */
    public function get_equipment_documents($equipment_id)
    {
        $this->db->where('equipment_id', $equipment_id);
        $this->db->order_by('document_type_id', 'ASC');
        return $this->db->get(db_prefix() . 'equipment_documents')->result_array();
    }

    /**
     * Get a single equipment document by ID
     */
    public function get_equipment_document($document_id)
    {
        $this->db->where('id', $document_id);
        return $this->db->get(db_prefix() . 'equipment_documents')->row();
    }

    /**
     * Get equipment document by equipment_id and document_type_id
     */
    public function get_equipment_document_by_type($equipment_id, $document_type_id)
    {
        $this->db->where('equipment_id', $equipment_id);
        $this->db->where('document_type_id', $document_type_id);
        return $this->db->get(db_prefix() . 'equipment_documents')->row();
    }

    /**
     * Save equipment document metadata without file
     */
    public function save_equipment_document_metadata($equipment_id, $document_type_id, $document_number, $expiry_date = null)
    {
        // Convert date format if needed
        if (!empty($expiry_date)) {
            $expiry_date = to_sql_date($expiry_date);
        }

        // Check if document already exists
        $existing = $this->get_equipment_document_by_type($equipment_id, $document_type_id);

        if ($existing) {
            // Update existing document metadata
            $update_data = [
                'document_number' => $document_number,
                'expiry_date'     => $expiry_date ?: null,
                'updated_at'      => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $existing->id);
            $this->db->update(db_prefix() . 'equipment_documents', $update_data);
            return true;
        } else {
            // Insert new document metadata (without file)
            $insert_data = [
                'equipment_id'     => $equipment_id,
                'document_type_id' => $document_type_id,
                'document_number'  => $document_number,
                'expiry_date'      => $expiry_date ?: null,
                'uploaded_by'      => get_staff_user_id(),
                'uploaded_at'      => date('Y-m-d H:i:s')
            ];

            $this->db->insert(db_prefix() . 'equipment_documents', $insert_data);
            return $this->db->insert_id();
        }
    }

    /**
     * Upload equipment document file
     */
    public function upload_equipment_document_file($equipment_id, $document_type_id, $file)
    {
        $CI = &get_instance();

        // Validate file type
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            return [
                'success' => false,
                'message' => _l('invalid_file_type')
            ];
        }

        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'File size exceeds 10MB limit'
            ];
        }

        // Create upload directory if not exists (using relative path)
        $upload_dir = 'uploads/equipments/' . $equipment_id . '/documents/';
        $upload_path_full = FCPATH . $upload_dir;

        if (!is_dir($upload_path_full)) {
            mkdir($upload_path_full, 0755, true);
        }

        // Generate unique filename
        $filename = 'doc_' . $document_type_id . '_' . time() . '.' . $file_ext;

        // Store RELATIVE path in database
        $file_path_relative = $upload_dir . $filename;

        // Use ABSOLUTE path for file operations
        $file_path_absolute = FCPATH . $file_path_relative;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path_absolute)) {
            return [
                'success' => false,
                'message' => _l('file_upload_failed')
            ];
        }

        // Get document data from POST
        $document_number = $CI->input->post('doc_number_' . $document_type_id);
        $expiry_date = $CI->input->post('doc_expiry_' . $document_type_id);

        // Convert date format if needed
        if (!empty($expiry_date)) {
            $expiry_date = to_sql_date($expiry_date);
        }

        // Check if document already exists for this equipment and document type
        $existing_doc = $this->db->where('equipment_id', $equipment_id)
            ->where('document_type_id', $document_type_id)
            ->get(db_prefix() . 'equipment_documents')
            ->row();

        if ($existing_doc) {
            // Delete old file if exists
            if (!empty($existing_doc->file_path)) {
                $old_file = FCPATH . $existing_doc->file_path;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Update existing document
            $update_data = [
                'file_name'       => $file['name'],
                'file_path'       => $file_path_relative,  // RELATIVE PATH
                'file_size'       => $file['size'],
                'document_number' => $document_number,
                'expiry_date'     => $expiry_date ?: null,
                'updated_at'      => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $existing_doc->id);
            $this->db->update(db_prefix() . 'equipment_documents', $update_data);
        } else {
            // Insert new document
            $insert_data = [
                'equipment_id'     => $equipment_id,
                'document_type_id' => $document_type_id,
                'file_name'        => $file['name'],
                'file_path'        => $file_path_relative,  // RELATIVE PATH
                'file_size'        => $file['size'],
                'document_number'  => $document_number,
                'expiry_date'      => $expiry_date ?: null,
                'uploaded_by'      => get_staff_user_id(),
                'uploaded_at'      => date('Y-m-d H:i:s')
            ];

            $this->db->insert(db_prefix() . 'equipment_documents', $insert_data);
        }

        return [
            'success' => true,
            'message' => _l('file_uploaded_successfully')
        ];
    }

    /**
     * Delete equipment document
     */
    public function delete_equipment_document($document_id)
    {
        // Get document info
        $document = $this->get_equipment_document($document_id);

        if (!$document) {
            return false;
        }

        // Delete physical file if exists (convert relative path to absolute)
        if (!empty($document->file_path)) {
            $file_path = FCPATH . $document->file_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Delete from database
        $this->db->where('id', $document_id);
        $this->db->delete(db_prefix() . 'equipment_documents');

        return $this->db->affected_rows() > 0;
    }
}
