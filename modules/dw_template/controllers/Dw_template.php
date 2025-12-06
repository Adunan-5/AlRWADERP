<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dw_template extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dw_template/dw_template_model');
        $this->load->language('dw_template/dw_template');
    }

    public function index()
    {
        $data['documents'] = $this->dw_template_model->get_all_documents();
        $data['letterheads'] = $this->dw_template_model->get_letterheads();
        $data['types'] = $this->dw_template_model->get_types();
        $data['title'] = _l('dw_document_templates');
        $this->load->view('dw_template/admin/manage', $data);
    }

    public function editor($id = null)
    {
        $data['letterheads'] = $this->dw_template_model->get_letterheads();
        $data['types'] = $this->dw_template_model->get_types();
        $data['title'] = $id ? _l('edit') . ' ' . _l('dw_document_template') : _l('add_new_dw_document_template');

        if ($id) {
            $data['document'] = $this->dw_template_model->get_document($id);
            if (!$data['document']) {
                set_alert('warning', 'Document not found');
                redirect(admin_url('dw_template'));
            }
            $letterhead = $this->db->where('id', $data['document']->letterhead_id)->get('tbl_letterheads')->row();
            $data['document']->letterhead_file_url = $letterhead ? base_url($letterhead->file) : '';
        } else {
            $data['document'] = null;
        }

        $this->load->view('dw_template/admin/editor', $data);
    }

    public function save()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $content = $this->input->post('content', false);
            $record = [
                'title'        => $this->input->post('title', true),
                'type_id'      => (int) $this->input->post('type_id'),
                'content'      => $content,
                'letterhead_id'=> (int) $this->input->post('letterhead_id') ?? null,
            ];

            if (!empty($post['id'])) {
                $this->dw_template_model->update_document($post['id'], $record);
                $id = $post['id'];
                set_alert('success', sprintf(_l('updated_successfully'), _l('dw_document_template')));
            } else {
                $id = $this->dw_template_model->add_document($record);
                set_alert('success', sprintf(_l('added_successfully'), _l('dw_document_template')));
            }

            redirect(admin_url('dw_template'));
        }
    }

    public function delete($id)
    {
        $this->dw_template_model->delete_document($id);
        set_alert('success', _l('dw_document_deleted_successfully'));
        redirect(admin_url('dw_template'));
    }

    public function download($id)
    {
        $doc = $this->dw_template_model->get_document($id);
        if (!$doc) {
            set_alert('warning', 'Document not found');
            redirect(admin_url('dw_template'));
        }

        if (!empty($doc->pdf_file) && file_exists(FCPATH . $doc->pdf_file)) {
            @unlink(FCPATH . $doc->pdf_file);
        }

        $pdf_file = $this->dw_template_model->generate_pdf($id);

        if (!$pdf_file || !file_exists(FCPATH . $pdf_file)) {
            set_alert('warning', 'PDF generation failed');
            redirect(admin_url('dw_template'));
        }

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
        $res = $this->dw_template_model->upload_letterhead();
        if ($res['success']) {
            set_alert('success', _l('upload_letterhead') . ' - OK');
        } else {
            set_alert('warning', $res['message']);
        }
        redirect(admin_url('dw_template'));
    }

    public function get_document($id)
    {
        $doc = $this->dw_template_model->get_document($id);
        if ($doc) {
            $letterhead = $this->db->where('id', $doc->letterhead_id)->get('tbl_letterheads')->row();
            $doc->letterhead_file_url = $letterhead ? base_url($letterhead->file) : '';
        }
        echo json_encode($doc);
    }

    public function get_type_template($type_id)
    {
        $type = $this->dw_template_model->get_type($type_id);
        if ($type) {
            $letterhead = $this->db->where('id', $type->default_letterhead_id)->get('tbl_letterheads')->row();
            $type->letterhead_file_url = $letterhead ? base_url($letterhead->file) : '';
        }
        echo json_encode($type);
    }

    public function save_type()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $template_content = $this->input->post('template_content', false);
            $data = [
                'key_name'              => $post['key_name'],
                'label'                 => $post['label'],
                'template_content'      => $template_content,
                'default_letterhead_id' => (int) $post['default_letterhead_id'] ?? null,
            ];

            if (!empty($post['id'])) {
                $this->dw_template_model->update_type($post['id'], $data);
                set_alert('success', sprintf(_l('updated_successfully'), _l('dw_document_type')));
            } else {
                $this->dw_template_model->add_type($data);
                set_alert('success', sprintf(_l('added_successfully'), _l('dw_document_type')));
            }
        }
        redirect(admin_url('dw_template'));
    }

    public function delete_type($id)
    {
        $is_used = $this->db->where('type_id', $id)->count_all_results('tbl_dw_documents');
        if ($is_used > 0) {
            set_alert('warning', _l('dw_document_type_in_use'));
        } else {
            $this->dw_template_model->delete_type($id);
            set_alert('success', _l('dw_document_type_deleted_successfully'));
        }
        redirect(admin_url('dw_template'));
    }
}