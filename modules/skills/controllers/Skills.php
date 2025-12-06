<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Skills extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('skills_module_model');
        $this->load->language('skills/skills');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('skills');
        }

        $data['title'] = _l('skills');
        $this->load->view('admin/manage', $data);
    }

    public function save()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = isset($data['id']) ? $data['id'] : null;
            unset($data['id']);

            if ($id) {
                $this->skills_module_model->update($data, $id);
                set_alert('success', 'Skill updated successfully');
            } else {
                $this->skills_module_model->add($data);
                set_alert('success', 'Skill added successfully');
            }
            redirect(admin_url('skills'));
        }
    }

    public function delete($id)
    {
        $this->skills_module_model->delete($id);
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => true]);
            die;
        }
        set_alert('success', 'Skill deleted');
        redirect(admin_url('skills'));
    }
}