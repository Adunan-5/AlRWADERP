<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Documentworkflow extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('documentworkflow/documentworkflow_model');
        $this->load->language('documentworkflow/documentworkflow');
    }

    public function index()
    {

        if (!staff_can('view', 'documentworkflow')) {
            access_denied('documentworkflow');
        }
        // Load letterheads, types, and documents for manage view
        $data['documents'] = $this->documentworkflow_model->get_all_documents();
        $data['letterheads'] = $this->documentworkflow_model->get_letterheads();
        $data['types'] = $this->documentworkflow_model->get_types();
        $data['title'] = _l('document_templates');
        $this->load->view('documentworkflow/admin/manage', $data);
    }

    public function editor($id = null)
    {

        if ($id && !staff_can('edit', 'documentworkflow')) {
            access_denied('documentworkflow');
        } elseif (!$id && !staff_can('create', 'documentworkflow')) {
            access_denied('documentworkflow');
        }
        // Load data for editor page (add or edit)
        $data['letterheads'] = $this->documentworkflow_model->get_letterheads();
        $data['types'] = $this->documentworkflow_model->get_types();
        $data['title'] = $id ? _l('edit') . ' ' . _l('document_template') : _l('add_new_document_template');

        if ($id) {
            $data['document'] = $this->documentworkflow_model->get_document($id);
            if (!$data['document']) {
                set_alert('warning', 'Document not found');
                redirect(admin_url('documentworkflow'));
            }
            $letterhead = $this->db->where('id', $data['document']->letterhead_id)->get('tbl_letterheads')->row();
            $data['document']->letterhead_file_url = $letterhead ? base_url($letterhead->file) : '';
        } else {
            $data['document'] = null;
        }

        $this->load->view('documentworkflow/admin/editor', $data);
    }

    public function save()
    {
        if ($this->input->post()) {

            $id = $this->input->post('id');
            if ($id && !staff_can('edit', 'documentworkflow')) {
                access_denied('documentworkflow');
            } elseif (!$id && !staff_can('create', 'documentworkflow')) {
                access_denied('documentworkflow');
            }
            $post = $this->input->post();

            $content = $this->input->post('content', false); // false = no XSS clean
            $record = [
                'title'        => $this->input->post('title', true),
                'type'         => $this->input->post('type', true),
                'content'      => $content,
                'letterhead_id'=> (int) $this->input->post('letterhead_id') ?? null,
            ];

            if (!empty($post['id'])) {
                $this->documentworkflow_model->update_document($post['id'], $record);
                $id = $post['id'];
                set_alert('success', sprintf(_l('updated_successfully'), _l('document_template')));
            } else {
                $id = $this->documentworkflow_model->add_document($record);
                set_alert('success', sprintf(_l('added_successfully'), _l('document_template')));
            }

            redirect(admin_url('documentworkflow'));
        }
    }

    public function delete($id)
    {

        if (!staff_can('delete', 'documentworkflow')) {
            access_denied('documentworkflow');
        }
        $this->documentworkflow_model->delete_document($id);
        set_alert('success', _l('document_deleted_successfully'));
        redirect(admin_url('documentworkflow'));
    }

    public function download($id)
    {

        if (!staff_can('view', 'documentworkflow')) {
            access_denied('documentworkflow');
        }
        $doc = $this->documentworkflow_model->get_document($id);
        if (!$doc) {
            set_alert('warning', 'Document not found');
            redirect(admin_url('documentworkflow'));
        }

        // Delete old PDF if exists
        if (!empty($doc->pdf_file) && file_exists(FCPATH . $doc->pdf_file)) {
            @unlink(FCPATH . $doc->pdf_file);
        }

        // Generate new PDF
        $pdf_file = $this->documentworkflow_model->generate_pdf($id);

        if (!$pdf_file || !file_exists(FCPATH . $pdf_file)) {
            set_alert('warning', 'PDF generation failed');
            redirect(admin_url('documentworkflow'));
        }

        // Serve PDF in browser
        $file_path = FCPATH . $pdf_file;
        $file_name = basename($pdf_file);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $file_name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        readfile($file_path);
        exit;
    }

    public function upload_letterhead()
    {
        $res = $this->documentworkflow_model->upload_letterhead();
        if ($res['success']) {
            set_alert('success', _l('upload_letterhead') . ' - OK');
        } else {
            set_alert('warning', $res['message']);
        }
        redirect(admin_url('documentworkflow'));
    }

    public function get_document($id)
    {
        $doc = $this->documentworkflow_model->get_document($id);
        if ($doc) {
            $letterhead = $this->db->where('id', $doc->letterhead_id)->get('tbl_letterheads')->row();
            $doc->letterhead_file_url = $letterhead ? base_url($letterhead->file) : '';
        }
        echo json_encode($doc);
    }

    public function save_type()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $data = [
                'key_name' => $post['key_name'],
                'label'    => $post['label']
            ];

            if (!empty($post['id'])) {
                $this->documentworkflow_model->update_type($post['id'], $data);
                set_alert('success', sprintf(_l('updated_successfully'), _l('document_type')));
            } else {
                $this->documentworkflow_model->add_type($data);
                set_alert('success', sprintf(_l('added_successfully'), _l('document_type')));
            }
        }
        redirect(admin_url('documentworkflow'));
    }

    public function delete_type($id)
    {
        // Check if type is used in any documents
        $is_used = $this->db->where('type', $this->db->where('id', $id)->get('tbl_document_types')->row()->key_name)->count_all_results('tbl_hr_documents');
        if ($is_used) {
            set_alert('warning', _l('document_type_in_use'));
        } else {
            $this->documentworkflow_model->delete_type($id);
            set_alert('success', _l('document_type_deleted_successfully'));
        }
        redirect(admin_url('documentworkflow'));
    }
}