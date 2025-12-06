<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Companytype extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('companytype_model');
        $this->load->language('companytype/companytype');
    }

    public function index()
    {

        if (!has_permission('companytype', '', 'view')) {
            access_denied('companytype');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('company_type');
        }

        $data['title'] = _l('company_types');
        $this->load->view('admin/manage', $data);
    }

    public function save()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = isset($data['id']) ? $data['id'] : null;
            unset($data['id']);

            if ($id) {
                if (!has_permission('companytype', '', 'edit')) {
                    access_denied('companytype');
                }
                $this->companytype_model->update($data, $id);
                set_alert('success', 'Company type updated successfully');
            } else {
                if (!has_permission('companytype', '', 'create')) {
                    access_denied('companytype');
                }
                $this->companytype_model->add($data);
                set_alert('success', 'Company type added successfully');
            }
            redirect(admin_url('companytype'));
        }
    }

    public function delete($id)
    {
        if (!has_permission('companytype', '', 'delete')) {
            access_denied('companytype');
        }
        $this->companytype_model->delete($id);
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => true]);
            die;
        }
        set_alert('success', 'Company type deleted');
        redirect(admin_url('companytype'));
    }
}