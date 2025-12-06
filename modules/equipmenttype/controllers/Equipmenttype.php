<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Equipmenttype extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipmenttype_model');
        $this->load->language('equipmenttype/equipmenttype');
    }

    // public function index()
    // {
    //     if ($this->input->is_ajax_request()) {
    //         $this->app->get_table_data('equipment_type');
    //     }

    //     $data['title'] = _l('equipment_types');
    //     $this->load->view('admin/manage', $data);
    // }

    // public function save()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post();
    //         $id = isset($data['id']) ? $data['id'] : null;
    //         unset($data['id']);

    //         if ($id) {
    //             $this->equipmenttype_model->update($data, $id);
    //             set_alert('success', 'Equipment type updated successfully');
    //         } else {
    //             $this->equipmenttype_model->add($data);
    //             set_alert('success', 'Equipment type added successfully');
    //         }
    //         redirect(admin_url('equipmenttype'));
    //     }
    // }

    // public function delete($id)
    // {
    //     $this->equipmenttype_model->delete($id);
    //     if ($this->input->is_ajax_request()) {
    //         echo json_encode(['success' => true]);
    //         die;
    //     }
    //     set_alert('success', 'Equipment type deleted');
    //     redirect(admin_url('equipmenttype'));
    // }

    public function index()
{
    if (!staff_can('view', 'equipmenttype')) {
        access_denied('equipmenttype');
    }

    if ($this->input->is_ajax_request()) {
        $this->app->get_table_data('equipment_type');
    }

    $data['title'] = _l('equipment_types');
    $this->load->view('admin/manage', $data);
}

public function save()
{
    if (!staff_can('create', 'equipmenttype') && !$this->input->post('id')) {
        access_denied('equipmenttype');
    }

    if (!staff_can('edit', 'equipmenttype') && $this->input->post('id')) {
        access_denied('equipmenttype');
    }

    if ($this->input->post()) {
        $data = $this->input->post();
        $id = isset($data['id']) ? $data['id'] : null;
        unset($data['id']);

        if ($id) {
            $this->equipmenttype_model->update($data, $id);
            set_alert('success', 'Equipment type updated successfully');
        } else {
            $this->equipmenttype_model->add($data);
            set_alert('success', 'Equipment type added successfully');
        }
        redirect(admin_url('equipmenttype'));
    }
}

public function delete($id)
{
    if (!staff_can('delete', 'equipmenttype')) {
        access_denied('equipmenttype');
    }

    $this->equipmenttype_model->delete($id);
    if ($this->input->is_ajax_request()) {
        echo json_encode(['success' => true]);
        die;
    }
    set_alert('success', 'Equipment type deleted');
    redirect(admin_url('equipmenttype'));
}
}