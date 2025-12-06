<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ownemployeetype extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ownemployeetype_model');
        $this->load->language('ownemployeetype/ownemployeetype');
    }

    public function index()
    {

        if (!has_permission('ownemployeetype', '', 'view')) {
            access_denied('Own Employee Type');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('ownemployee_type');
        }

        $data['title'] = _l('ownemployee_types');
        $this->load->view('admin/manage', $data);
    }

    public function save()
    {
        $data = $this->input->post();
        $id = isset($data['id']) ? $data['id'] : null;

        if ($id && !has_permission('ownemployeetype', '', 'edit')) {
            access_denied('Own Employee Type');
        } elseif (!$id && !has_permission('ownemployeetype', '', 'create')) {
            access_denied('Own Employee Type');
        }

        unset($data['id']);

        if ($id) {
            $this->ownemployeetype_model->update($data, $id);
            set_alert('success', 'Own Employee type updated successfully');
        } else {
            $this->ownemployeetype_model->add($data);
            set_alert('success', 'Own Employee type added successfully');
        }

        redirect(admin_url('ownemployeetype'));
    }

    public function delete($id)
    {

        if (!has_permission('ownemployeetype', '', 'delete')) {
            access_denied('Own Employee Type');
        }
        $this->ownemployeetype_model->delete($id);
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => true]);
            die;
        }
        set_alert('success', 'Own Employee type deleted');
        redirect(admin_url('ownemployeetype'));
    }
}