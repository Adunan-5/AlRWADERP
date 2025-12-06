<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Document_types extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('document_types_model');
    }

    /**
     * List all document types
     */
    public function index()
    {
        if (!is_admin()) {
            access_denied('Document Types');
        }

        $data['title'] = 'Document Types';
        $this->load->view('admin/document_types/manage', $data);
    }

    /**
     * Add or update document type
     */
    public function save()
    {
        if (!is_admin()) {
            access_denied('Document Types');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->input->post('id');

            // Check for duplicate name
            $is_duplicate = $this->document_types_model->check_duplicate($data['name'], $id);
            if ($is_duplicate) {
                set_alert('danger', 'A document type with this name already exists.');
                redirect(admin_url('document_types'));
                return;
            }

            if ($id) {
                // Update
                $success = $this->document_types_model->update($id, $data);
                if ($success) {
                    set_alert('success', 'Document type updated successfully.');
                } else {
                    set_alert('warning', 'No changes were made.');
                }
            } else {
                // Add
                $id = $this->document_types_model->add($data);
                if ($id) {
                    set_alert('success', 'Document type added successfully.');
                } else {
                    set_alert('danger', 'Failed to add document type.');
                }
            }
        }

        redirect(admin_url('document_types'));
    }

    /**
     * Quick add document type (AJAX)
     */
    public function quick_add()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $name = trim($this->input->post('name'));

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Document type name is required.']);
            return;
        }

        // Check if already exists
        $is_duplicate = $this->document_types_model->check_duplicate($name);
        if ($is_duplicate) {
            // Get existing ID
            $existing = $this->db->where('name', $name)->get(db_prefix() . 'document_types')->row();
            echo json_encode([
                'success' => true,
                'id' => $existing->id,
                'name' => $existing->name,
                'message' => 'Document type already exists.'
            ]);
            return;
        }

        // Add new document type
        $data = [
            'name' => $name,
            'description' => $this->input->post('description') ?? '',
            'is_system' => 0
        ];

        $id = $this->document_types_model->add($data);

        if ($id) {
            echo json_encode([
                'success' => true,
                'id' => $id,
                'name' => $name,
                'message' => 'Document type added successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add document type.'
            ]);
        }
    }

    /**
     * Delete document type
     */
    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Document Types');
        }

        if (!$id) {
            redirect(admin_url('document_types'));
        }

        $result = $this->document_types_model->delete($id);

        if (is_array($result)) {
            // Error occurred
            set_alert('danger', $result['message']);
        } elseif ($result === true) {
            set_alert('success', 'Document type deleted successfully.');
        } else {
            set_alert('danger', 'Failed to delete document type.');
        }

        redirect(admin_url('document_types'));
    }

    /**
     * Get all document types for dropdown (AJAX)
     */
    public function get_all()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $document_types = $this->document_types_model->get();
        echo json_encode($document_types);
    }
}
