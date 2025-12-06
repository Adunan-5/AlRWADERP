<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipments extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipments_model');
        $this->load->model('operators_model');
        $this->load->model('equipment_timesheet_model');
        $this->load->model('equipment_mobilization_model');
        $this->load->language('equipments/equipments');
    }

    public function index()
    {
        if (!has_permission('equipments', '', 'view')) {
            access_denied('Equipments');
        }

        $data['title'] = _l('equipments');
        $this->load->view('admin/manage', $data);
    }

    /**
     * Get equipments for DataTables
     */
    public function table_equipments()
    {
        if (!has_permission('equipments', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/tables/equipments'));
    }

    public function add()
    {
        if (!has_permission('equipments', '', 'create')) {
            access_denied('Equipments');
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();

            // Filter out document-related fields (they're handled separately)
            $equipment_data = [];
            foreach ($post_data as $key => $value) {
                // Exclude document fields (doc_number_X, doc_expiry_X, doc_file_X)
                if (strpos($key, 'doc_number_') !== 0 &&
                    strpos($key, 'doc_expiry_') !== 0 &&
                    strpos($key, 'doc_file_') !== 0) {
                    $equipment_data[$key] = $value;
                }
            }

            if (isset($equipment_data['equipmenttype']) && is_array($equipment_data['equipmenttype'])) {
                $equipment_data['equipmenttype'] = implode(',', $equipment_data['equipmenttype']);
            }

            $equipment_id = $this->equipments_model->add($equipment_data);
            $success = (bool)$equipment_id;
            $message = $success ? _l('added_successfully', _l('equipment')) : _l('added_fail', _l('equipment'));

            // Handle documents (both file uploads and metadata)
            if ($success && $equipment_id) {
                $upload_errors = [];

                // Process file uploads
                foreach ($_FILES as $field_name => $file) {
                    // Check if this is a document file (doc_file_X)
                    if (strpos($field_name, 'doc_file_') === 0 && $file['error'] == UPLOAD_ERR_OK) {
                        $doc_type_id = str_replace('doc_file_', '', $field_name);

                        // Upload the file
                        $upload_result = $this->equipments_model->upload_equipment_document_file($equipment_id, $doc_type_id, $file);

                        if (!$upload_result['success']) {
                            $upload_errors[] = $upload_result['message'];
                        }
                    }
                }

                // Save document metadata (number, expiry) even without file upload
                foreach ($post_data as $key => $value) {
                    if (strpos($key, 'doc_number_') === 0 && !empty($value)) {
                        $doc_type_id = str_replace('doc_number_', '', $key);
                        $expiry_date = $this->input->post('doc_expiry_' . $doc_type_id);

                        // Check if document doesn't already exist (file upload would have created it)
                        $existing = $this->equipments_model->get_equipment_document_by_type($equipment_id, $doc_type_id);

                        if (!$existing) {
                            // Save metadata without file
                            $this->equipments_model->save_equipment_document_metadata($equipment_id, $doc_type_id, $value, $expiry_date);
                        }
                    }
                }

                // Show upload errors if any
                if (!empty($upload_errors)) {
                    set_alert('warning', $message . '<br>' . implode('<br>', $upload_errors));
                } else {
                    set_alert('success', $message);
                }
            } else {
                if ($success) {
                    set_alert('success', $message);
                } else {
                    set_alert('danger', $message);
                }
            }
            redirect(admin_url('equipments'));
        }

        // Get equipment document types from master table
        $data['equipment_document_types'] = $this->equipments_model->get_equipment_document_types();
        $data['title'] = _l('add_new_equipment');
        $this->load->view('admin/equipments', $data);
    }


    public function edit($id)
    {
        if (!has_permission('equipments', '', 'edit')) {
            access_denied('Equipments');
        }
        $equipment = $this->equipments_model->get($id);
        if (!$equipment) {
            show_404();
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();

            // Filter out document-related fields (they're handled separately)
            $equipment_data = [];
            foreach ($post_data as $key => $value) {
                // Exclude document fields (doc_number_X, doc_expiry_X, doc_file_X)
                if (strpos($key, 'doc_number_') !== 0 &&
                    strpos($key, 'doc_expiry_') !== 0 &&
                    strpos($key, 'doc_file_') !== 0) {
                    $equipment_data[$key] = $value;
                }
            }

            if (isset($equipment_data['equipmenttype']) && is_array($equipment_data['equipmenttype'])) {
                $equipment_data['equipmenttype'] = implode(',', $equipment_data['equipmenttype']);
            }

            $success = $this->equipments_model->update($equipment_data, $id);
            $message = $success ? _l('updated_successfully', _l('equipment')) : _l('updated_fail', _l('equipment'));

            // Handle documents (both file uploads and metadata)
            if ($success) {
                $upload_errors = [];

                // Process file uploads
                foreach ($_FILES as $field_name => $file) {
                    // Check if this is a document file (doc_file_X)
                    if (strpos($field_name, 'doc_file_') === 0 && $file['error'] == UPLOAD_ERR_OK) {
                        $doc_type_id = str_replace('doc_file_', '', $field_name);

                        // Upload the file
                        $upload_result = $this->equipments_model->upload_equipment_document_file($id, $doc_type_id, $file);

                        if (!$upload_result['success']) {
                            $upload_errors[] = $upload_result['message'];
                        }
                    }
                }

                // Save document metadata (number, expiry) even without file upload
                foreach ($post_data as $key => $value) {
                    if (strpos($key, 'doc_number_') === 0 && !empty($value)) {
                        $doc_type_id = str_replace('doc_number_', '', $key);
                        $expiry_date = $this->input->post('doc_expiry_' . $doc_type_id);

                        // Check if document doesn't already exist (file upload would have created it)
                        $existing = $this->equipments_model->get_equipment_document_by_type($id, $doc_type_id);

                        if (!$existing) {
                            // Save metadata without file
                            $this->equipments_model->save_equipment_document_metadata($id, $doc_type_id, $value, $expiry_date);
                        } else {
                            // Update existing metadata
                            $this->equipments_model->save_equipment_document_metadata($id, $doc_type_id, $value, $expiry_date);
                        }
                    }
                }

                // Show upload errors if any
                if (!empty($upload_errors)) {
                    set_alert('warning', $message . '<br>' . implode('<br>', $upload_errors));
                } else {
                    set_alert('success', $message);
                }
            } else {
                set_alert('danger', $message);
            }
            redirect(admin_url('equipments'));
        }

        $data['equipment'] = $equipment;
        $data['title'] = _l('edit') . ' ' . $equipment->name;

        // Get equipment document types from master table
        $data['equipment_document_types'] = $this->equipments_model->get_equipment_document_types();

        // Get existing equipment documents
        $data['equipment_documents'] = $this->equipments_model->get_equipment_documents($id);

        $this->load->view('admin/equipments', $data);
    }


    public function delete($id)
    {

        if (!has_permission('equipments', '', 'delete')) {
            access_denied('Equipments');
        }
        $this->equipments_model->delete($id);
        set_alert('success', 'Equipment deleted');
        redirect(admin_url('equipments'));
    }

    /**
     * Download equipment document file
     */
    public function download_equipment_document($document_id)
    {
        if (!has_permission('equipments', '', 'view')) {
            access_denied('Equipments');
        }

        $document = $this->equipments_model->get_equipment_document($document_id);

        if (!$document) {
            set_alert('danger', _l('document_not_found'));
            redirect(admin_url('equipments'));
        }

        // Build absolute path from relative path stored in database
        $file_path = FCPATH . $document->file_path;

        if (!file_exists($file_path)) {
            set_alert('danger', _l('document_not_found'));
            redirect(admin_url('equipments'));
        }

        // Force download
        $this->load->helper('download');
        force_download($document->file_name, file_get_contents($file_path));
    }

    /**
     * Delete equipment document
     */
    public function delete_equipment_document($document_id)
    {
        if (!has_permission('equipments', '', 'edit')) {
            access_denied('Equipments');
        }

        $document = $this->equipments_model->get_equipment_document($document_id);

        if (!$document) {
            set_alert('danger', _l('document_not_found'));
            redirect(admin_url('equipments'));
        }

        $equipment_id = $document->equipment_id;

        if ($this->equipments_model->delete_equipment_document($document_id)) {
            set_alert('success', _l('file_deleted_successfully'));
        } else {
            set_alert('danger', _l('problem_deleting'));
        }

        redirect(admin_url('equipments/edit/' . $equipment_id));
    }

    // ===== OPERATORS MANAGEMENT =====

    public function operators()
    {
        if (!has_permission('operators', '', 'view')) {
            access_denied('Operators');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('operators');
        }

        $data['title'] = _l('operators');
        $this->load->view('admin/operators/manage', $data);
    }

    public function operator($id = '')
    {
        if ($this->input->post()) {
            if (!has_permission('operators', '', $id ? 'edit' : 'create')) {
                access_denied('Operators');
            }

            $data = $this->input->post();

            // DEBUG: Log submitted data
            log_activity('Operator Form Submission - Data: ' . json_encode($data));

            // Auto-fix: Check if required columns exist, if not add them
            if (!$this->db->field_exists('is_active', db_prefix() . 'operators')) {
                log_activity('Auto-fixing operators table - adding missing columns');

                $this->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `status`');
                $this->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD KEY `idx_is_active` (`is_active`)');

                if (!$this->db->field_exists('updated_by', db_prefix() . 'operators')) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD COLUMN `updated_by` INT(11) DEFAULT NULL AFTER `updated_at`');
                }

                if (!$this->db->field_exists('remarks', db_prefix() . 'operators')) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD COLUMN `remarks` TEXT DEFAULT NULL AFTER `notes`');
                }

                log_activity('Operators table structure fixed successfully');
            }

            if ($id) {
                $success = $this->operators_model->update($data, $id);
                $message = $success ? _l('updated_successfully', _l('operator')) : _l('updated_fail', _l('operator'));
                $operator_id = $id;
            } else {
                $new_id = $this->operators_model->add($data);
                $message = $new_id ? _l('added_successfully', _l('operator')) : _l('added_fail', _l('operator'));
                $success = (bool)$new_id;
                $operator_id = $new_id;
            }

            // Handle file uploads for documents
            if ($success && $operator_id) {
                $upload_errors = [];
                foreach ($_FILES as $field_name => $file) {
                    // Check if this is a document file (doc_file_X)
                    if (strpos($field_name, 'doc_file_') === 0 && $file['error'] == UPLOAD_ERR_OK) {
                        $doc_type_id = str_replace('doc_file_', '', $field_name);

                        // Upload the file
                        $upload_result = $this->operators_model->upload_document_file($operator_id, $doc_type_id, $file);

                        if (!$upload_result['success']) {
                            $upload_errors[] = $upload_result['message'];
                        }
                    }
                }

                // Show upload errors if any
                if (!empty($upload_errors)) {
                    set_alert('warning', $message . '<br>' . implode('<br>', $upload_errors));
                } else {
                    set_alert('success', $message);
                }
            } else {
                if ($success) {
                    set_alert('success', $message);
                } else {
                    set_alert('danger', $message);
                }
            }
            redirect(admin_url('equipments/operators'));
        }

        if ($id) {
            if (!has_permission('operators', '', 'view')) {
                access_denied('Operators');
            }
            $data['operator'] = $this->operators_model->get($id);
            if (!$data['operator']) {
                show_404();
            }
            $data['title'] = _l('edit') . ' ' . _l('operator');
        } else {
            if (!has_permission('operators', '', 'create')) {
                access_denied('Operators');
            }
            $data['title'] = _l('add_new_operator');
        }

        // Get suppliers for dropdown
        $data['suppliers'] = $this->db->get(db_prefix() . 'suppliers')->result_array();

        // Get countries for nationality dropdown
        $data['countries'] = get_all_countries();

        // Get document types from master table
        $data['document_types'] = $this->operators_model->get_document_types();

        // Get existing operator documents if editing
        if ($id) {
            $data['operator_documents'] = $this->operators_model->get_documents($id);
        }

        $this->load->view('admin/operators/operator', $data);
    }

    public function delete_operator($id)
    {
        if (!has_permission('operators', '', 'delete')) {
            access_denied('Operators');
        }

        $result = $this->operators_model->delete($id);

        if (is_array($result) && !$result['success']) {
            set_alert('danger', $result['message']);
        } elseif ($result) {
            set_alert('success', _l('deleted', _l('operator')));
        } else {
            set_alert('danger', _l('problem_deleting', _l('operator')));
        }
        redirect(admin_url('equipments/operators'));
    }

    /**
     * Download operator document
     */
    public function download_operator_document($document_id)
    {
        if (!has_permission('operators', '', 'view')) {
            access_denied('Operators');
        }

        $document = $this->db->where('id', $document_id)
            ->get(db_prefix() . 'operator_documents')
            ->row();

        if (!$document) {
            set_alert('danger', _l('document_not_found'));
            redirect(admin_url('equipments/operators'));
        }

        // Build absolute path from relative path stored in database
        $file_path = FCPATH . $document->file_path;

        if (!file_exists($file_path)) {
            set_alert('danger', _l('document_not_found'));
            redirect(admin_url('equipments/operators'));
        }

        // Force download
        $this->load->helper('download');
        force_download($document->file_name, file_get_contents($file_path));
    }

    /**
     * Delete operator document file
     */
    public function delete_operator_document($document_id)
    {
        if (!has_permission('operators', '', 'edit')) {
            access_denied('Operators');
        }

        $result = $this->operators_model->delete_document($document_id);

        if ($result) {
            set_alert('success', _l('file_deleted_successfully'));
        } else {
            set_alert('danger', _l('document_not_found'));
        }

        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('equipments/operators'));
    }

    /**
     * Toggle operator status (active/inactive)
     */
    public function toggle_operator_status()
    {
        if (!has_permission('operators', '', 'edit')) {
            ajax_access_denied();
        }

        $id = $this->input->post('id');
        $status = $this->input->post('status');

        if (!$id || !isset($status)) {
            echo json_encode(['success' => false, 'message' => _l('something_went_wrong')]);
            return;
        }

        $result = $this->operators_model->change_status($id, $status);

        if ($result) {
            echo json_encode(['success' => true, 'message' => _l('status_changed_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('something_went_wrong')]);
        }
    }

    /**
     * Get operators for DataTables
     */
    public function table_operators()
    {
        if (!has_permission('operators', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/tables/operators'));
    }

    /**
     * Change operator status
     */
    public function change_operator_status()
    {
        if ($this->input->post() && has_permission('operators', '', 'edit')) {
            $id = $this->input->post('id');
            $status = $this->input->post('status');

            $success = $this->operators_model->change_status($id, $status);

            echo json_encode([
                'success' => $success,
                'message' => $success ? _l('status_changed_successfully') : _l('something_went_wrong')
            ]);
        }
    }

    /**
     * Get operators by supplier (AJAX)
     */
    public function get_operators_by_supplier($supplier_id)
    {
        if (!has_permission('operators', '', 'view')) {
            ajax_access_denied();
        }

        $operators = $this->operators_model->get_by_supplier($supplier_id);
        echo json_encode($operators);
    }

    // ===== MOBILIZATION MANAGEMENT =====

    public function mobilization()
    {
        if (!has_permission('equipment_mobilization', '', 'view')) {
            access_denied('Mobilization');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('equipment_mobilization');
        }

        $data['title'] = _l('mobilization');
        $this->load->view('admin/mobilization/manage', $data);
    }

    // ===== TIMESHEETS MANAGEMENT =====

    public function timesheets()
    {
        if (!has_permission('equipment_timesheets', '', 'view')) {
            access_denied('Timesheets');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('equipment_timesheet');
        }

        $data['title'] = _l('equipment_timesheets');
        $this->load->view('admin/timesheets/manage', $data);
    }

    // ===== AGREEMENTS MANAGEMENT =====

    public function agreements()
    {
        if (!has_permission('equipment_agreements', '', 'view')) {
            access_denied('Agreements');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('equipment_agreements');
        }

        $data['title'] = _l('equipment_agreements');
        $this->load->view('admin/agreements/manage', $data);
    }

    // ===== SETTINGS =====

    public function settings()
    {
        if (!is_admin()) {
            access_denied('Settings');
        }

        $data['title'] = _l('equipment_settings');
        $this->load->view('admin/settings/manage', $data);
    }
}
