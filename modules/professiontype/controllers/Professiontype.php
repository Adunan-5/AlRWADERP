<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Professiontype extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // ✅ Fix: load the correct model file and give it an alias
        $this->load->model('professiontype/ProfessionType_model', 'professiontype_model');
        $this->load->language('professiontype/professiontype');
    }

    public function index()
    {

        if (!has_permission('professiontype', '', 'view')) {
            access_denied('Profession Type');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('profession_type');
        }

        $data['title'] = _l('profession_types');
        $this->load->view('admin/manage', $data);
    }

    public function save()
    {
        $data = $this->input->post();
        $id = isset($data['id']) ? $data['id'] : null;

        if ($id && !has_permission('professiontype', '', 'edit')) {
            access_denied('Profession Type');
        } elseif (!$id && !has_permission('professiontype', '', 'create')) {
            access_denied('Profession Type');
        }

        unset($data['id']);

        if ($id) {
            $this->professiontype_model->update($data, $id);
            set_alert('success', 'Profession Type updated successfully');
        } else {
            $this->professiontype_model->add($data);
            set_alert('success', 'Profession Type added successfully');
        }

        redirect(admin_url('professiontype'));
    }

    public function delete($id)
    {

        if (!has_permission('professiontype', '', 'delete')) {
            access_denied('Profession Type');
        }
        $this->professiontype_model->delete($id);
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => true]);
            die;
        }
        set_alert('success', 'Profession type deleted');
        redirect(admin_url('professiontype'));
    }
}
