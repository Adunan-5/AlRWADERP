<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Operators model
 * Manages equipment operators/drivers
 */
class Operators_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get operators
     * @param  mixed $id    optional operator id
     * @param  array $where additional where conditions
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('op.*,
            s.name as supplier_name,
            s.display_name as supplier_display_name');
        $this->db->from(db_prefix() . 'operators op');
        $this->db->join(db_prefix() . 'suppliers s', 's.id = op.supplier_id', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        if (is_numeric($id)) {
            $this->db->where('op.id', $id);
            return $this->db->get()->row();
        }

        $this->db->order_by('op.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Add new operator
     * @param array $data operator data
     * @return int insert ID
     */
    public function add($data)
    {
        // Extract document fields before inserting operator
        $documents = $this->extract_document_fields($data);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        // Handle checkboxes
        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $data = hooks()->apply_filters('before_operator_added', $data);

        $this->db->insert(db_prefix() . 'operators', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Save documents
            $this->save_operator_documents($insert_id, $documents);

            hooks()->do_action('operator_created', $insert_id);
            log_activity('New Operator Added [' . $data['name'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update operator
     * @param  array $data operator data
     * @param  int   $id   operator id
     * @return bool
     */
    public function update($data, $id)
    {
        // Extract document fields before updating operator
        $documents = $this->extract_document_fields($data);

        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();

        // Handle checkboxes
        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        $data = hooks()->apply_filters('before_operator_updated', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'operators', $data);

        // Always save documents even if operator data didn't change
        $this->save_operator_documents($id, $documents);

        if ($this->db->affected_rows() > 0 || !empty($documents)) {
            hooks()->do_action('operator_updated', $id);
            log_activity('Operator Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete operator
     * @param  int $id operator id
     * @return bool
     */
    public function delete($id)
    {
        $operator = $this->get($id);

        if (!$operator) {
            return false;
        }

        hooks()->do_action('before_delete_operator', $id);

        // Check if operator is assigned to any equipment or timesheet
        $this->db->where('operator_id', $id);
        $assigned_count = $this->db->count_all_results(db_prefix() . 'equipment_operators');

        if ($assigned_count > 0) {
            return [
                'success' => false,
                'message' => _l('operator_has_assignments')
            ];
        }

        // Delete operator documents first
        $this->db->where('operator_id', $id);
        $this->db->delete(db_prefix() . 'operator_documents');

        // Delete the operator
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'operators');

        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('operator_deleted', $id);
            log_activity('Operator Deleted [' . $operator->name . ']');
            return true;
        }

        return false;
    }

    /**
     * Get operators by supplier
     * @param  int $supplier_id
     * @return array
     */
    public function get_by_supplier($supplier_id)
    {
        return $this->get('', ['op.supplier_id' => $supplier_id, 'op.is_active' => 1]);
    }

    /**
     * Get operators by type
     * @param  string $type (own/hired)
     * @return array
     */
    public function get_by_type($type)
    {
        return $this->get('', ['op.operator_type' => $type, 'op.is_active' => 1]);
    }

    /**
     * Get operators with expiring documents
     * @param  int $days_ahead number of days to check ahead
     * @return array
     */
    public function get_with_expiring_documents($days_ahead = 30)
    {
        $future_date = date('Y-m-d', strtotime('+' . $days_ahead . ' days'));
        $today = date('Y-m-d');

        $this->db->select('op.*,
            od.id as document_id,
            od.document_type_id,
            od.expiry_date,
            odt.name as document_type_name,
            odt.name_arabic as document_type_name_arabic,
            DATEDIFF(od.expiry_date, "' . $today . '") as days_until_expiry');
        $this->db->from(db_prefix() . 'operators op');
        $this->db->join(db_prefix() . 'operator_documents od', 'od.operator_id = op.id', 'inner');
        $this->db->join(db_prefix() . 'operator_document_types odt', 'odt.id = od.document_type_id', 'left');
        $this->db->where('od.expiry_date >=', $today);
        $this->db->where('od.expiry_date <=', $future_date);
        $this->db->where('op.is_active', 1);
        $this->db->order_by('od.expiry_date', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get operator documents
     * @param  int $operator_id
     * @return array
     */
    public function get_documents($operator_id)
    {
        $this->db->select('od.*,
            odt.name as document_type_name,
            odt.name_arabic as document_type_name_arabic,
            odt.requires_expiry,
            odt.reminder_days');
        $this->db->from(db_prefix() . 'operator_documents od');
        $this->db->join(db_prefix() . 'operator_document_types odt', 'odt.id = od.document_type_id', 'left');
        $this->db->where('od.operator_id', $operator_id);
        $this->db->order_by('od.uploaded_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Add operator document
     * @param array $data document data
     * @return int insert ID
     */
    public function add_document($data)
    {
        $data['uploaded_at'] = date('Y-m-d H:i:s');
        $data['uploaded_by'] = get_staff_user_id();

        $this->db->insert(db_prefix() . 'operator_documents', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('Operator Document Added [Operator ID: ' . $data['operator_id'] . ']');
        }

        return $insert_id;
    }

    /**
     * Delete operator document
     * @param  int $document_id
     * @return bool
     */
    public function delete_document($document_id)
    {
        $document = $this->db->where('id', $document_id)->get(db_prefix() . 'operator_documents')->row();

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

        $this->db->where('id', $document_id);
        $this->db->delete(db_prefix() . 'operator_documents');

        if ($this->db->affected_rows() > 0) {
            log_activity('Operator Document Deleted [ID: ' . $document_id . ']');
            return true;
        }

        return false;
    }

    /**
     * Get operator document types
     * @param  int $id optional type id
     * @return mixed
     */
    public function get_document_types($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'operator_document_types')->row();
        }

        $this->db->where('active', 1);
        $this->db->order_by('display_order', 'ASC');
        return $this->db->get(db_prefix() . 'operator_document_types')->result_array();
    }

    /**
     * Get operators count by status
     * @return array
     */
    public function get_statistics()
    {
        $stats = [];

        // Total active operators
        $this->db->where('is_active', 1);
        $stats['total_active'] = $this->db->count_all_results(db_prefix() . 'operators');

        // By type
        $this->db->where('is_active', 1);
        $this->db->where('operator_type', 'own');
        $stats['own_operators'] = $this->db->count_all_results(db_prefix() . 'operators');

        $this->db->where('is_active', 1);
        $this->db->where('operator_type', 'hired');
        $stats['hired_operators'] = $this->db->count_all_results(db_prefix() . 'operators');

        // Expiring documents count (next 30 days)
        $future_date = date('Y-m-d', strtotime('+30 days'));
        $today = date('Y-m-d');

        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $future_date);
        $stats['expiring_documents'] = $this->db->count_all_results(db_prefix() . 'operator_documents');

        return $stats;
    }

    /**
     * Change operator status
     * @param  int    $id     operator id
     * @param  int    $status 1 or 0
     * @return bool
     */
    public function change_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'operators', [
            'is_active' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id()
        ]);

        if ($this->db->affected_rows() > 0) {
            $status_text = $status == 1 ? 'Activated' : 'Deactivated';
            log_activity('Operator ' . $status_text . ' [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Extract document fields from POST data
     * @param  array &$data reference to data array
     * @return array extracted documents
     */
    private function extract_document_fields(&$data)
    {
        $documents = [];

        foreach ($data as $key => $value) {
            // Extract doc_number_X fields
            if (strpos($key, 'doc_number_') === 0) {
                $doc_type_id = str_replace('doc_number_', '', $key);
                if (!isset($documents[$doc_type_id])) {
                    $documents[$doc_type_id] = [];
                }
                $documents[$doc_type_id]['document_number'] = $value;
                unset($data[$key]);
            }

            // Extract doc_expiry_X fields
            if (strpos($key, 'doc_expiry_') === 0) {
                $doc_type_id = str_replace('doc_expiry_', '', $key);
                if (!isset($documents[$doc_type_id])) {
                    $documents[$doc_type_id] = [];
                }
                $documents[$doc_type_id]['expiry_date'] = $value ? date('Y-m-d', strtotime($value)) : null;
                unset($data[$key]);
            }
        }

        return $documents;
    }

    /**
     * Save operator documents
     * @param  int   $operator_id
     * @param  array $documents
     * @return void
     */
    private function save_operator_documents($operator_id, $documents)
    {
        foreach ($documents as $doc_type_id => $doc_data) {
            // Skip if no document number provided
            if (empty($doc_data['document_number'])) {
                continue;
            }

            // Check if document already exists
            $this->db->where('operator_id', $operator_id);
            $this->db->where('document_type_id', $doc_type_id);
            $existing = $this->db->get(db_prefix() . 'operator_documents')->row();

            $save_data = [
                'operator_id' => $operator_id,
                'document_type_id' => $doc_type_id,
                'document_number' => $doc_data['document_number'],
                'expiry_date' => isset($doc_data['expiry_date']) ? $doc_data['expiry_date'] : null,
            ];

            if ($existing) {
                // Update existing document
                $save_data['updated_at'] = date('Y-m-d H:i:s');

                $this->db->where('id', $existing->id);
                $this->db->update(db_prefix() . 'operator_documents', $save_data);
            } else {
                // Insert new document
                $save_data['uploaded_at'] = date('Y-m-d H:i:s');
                $save_data['uploaded_by'] = get_staff_user_id();
                // Set empty file fields for now (documents without files)
                $save_data['file_name'] = '';
                $save_data['file_path'] = '';

                $this->db->insert(db_prefix() . 'operator_documents', $save_data);
            }
        }
    }

    /**
     * Upload document file
     * @param  int    $operator_id
     * @param  int    $document_type_id
     * @param  array  $file $_FILES array for the upload
     * @return array  success status and message
     */
    public function upload_document_file($operator_id, $document_type_id, $file)
    {
        // Create upload directory if not exists (absolute path for file operations)
        $upload_dir = 'uploads/operators/' . $operator_id . '/documents/';
        $upload_path_full = FCPATH . $upload_dir;

        if (!is_dir($upload_path_full)) {
            mkdir($upload_path_full, 0755, true);
        }

        // Validate file type
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
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

        // Generate unique filename
        $filename = 'doc_' . $document_type_id . '_' . time() . '.' . $file_extension;

        // Store RELATIVE path in database
        $file_path_relative = $upload_dir . $filename;

        // Use ABSOLUTE path for file operations
        $file_path_absolute = FCPATH . $file_path_relative;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path_absolute)) {
            // Update database
            $this->db->where('operator_id', $operator_id);
            $this->db->where('document_type_id', $document_type_id);
            $document = $this->db->get(db_prefix() . 'operator_documents')->row();

            if ($document) {
                // Delete old file if exists
                if (!empty($document->file_path)) {
                    $old_file_path = FCPATH . $document->file_path;
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }

                // Update with new file (SAVE RELATIVE PATH)
                $this->db->where('id', $document->id);
                $this->db->update(db_prefix() . 'operator_documents', [
                    'file_name' => $file['name'],
                    'file_path' => $file_path_relative,  // RELATIVE PATH
                    'file_size' => $file['size'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                return [
                    'success' => true,
                    'message' => _l('file_uploaded_successfully')
                ];
            }
        }

        return [
            'success' => false,
            'message' => _l('file_upload_failed')
        ];
    }
}
