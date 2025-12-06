<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employeetype extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('employeetype_model');
        $this->load->language('employeetype/employeetype');
    }

    public function index()
    {

        if (!has_permission('employeetype', '', 'view')) {
            access_denied('Employee Type');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('employee_type');
        }

        $data['title'] = _l('employee_types');
        $this->load->view('admin/manage', $data);
    }

    public function save()
    {
        if ($this->input->post()) {

            if ($id && !has_permission('employeetype', '', 'edit')) {
                access_denied('Employee Type');
            } elseif (!$id && !has_permission('employeetype', '', 'create')) {
                access_denied('Employee Type');
            }
            $data = $this->input->post();
            $id = isset($data['id']) ? $data['id'] : null;
            unset($data['id']);

            if ($id) {
                $this->employeetype_model->update($data, $id);
                set_alert('success', 'Employee type updated successfully');
            } else {
                $this->employeetype_model->add($data);
                set_alert('success', 'Employee type added successfully');
            }
            redirect(admin_url('employeetype'));
        }
    }

    public function delete($id)
    {

        if (!has_permission('employeetype', '', 'delete')) {
            access_denied('Employee Type');
        }
        $this->employeetype_model->delete($id);
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => true]);
            die;
        }
        set_alert('success', 'Employee type deleted');
        redirect(admin_url('employeetype'));
    }
}