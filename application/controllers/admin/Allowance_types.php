<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Allowance_types extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Only admins can access this module
        if (!is_admin()) {
            access_denied('Allowance Types');
        }

        $this->load->model('allowance_types_model');
        $this->load->model('allowance_assignments_model');
    }

    /**
     * List all allowance types
     */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->get_all();
            return;
        }

        $data['title'] = _l('allowance_types');
        $data['allowances'] = $this->allowance_types_model->get_with_stats();

        // Get staff types, company types and profession types for assignments
        $this->load->helper('stafftype_helper');
        $this->load->helper('company_type_helper');
        $this->load->helper('profession_type_helper');
        $data['staff_types'] = get_all_stafftypes();
        $data['company_types'] = get_all_company_types();
        $data['profession_types'] = get_all_profession_types();

        $this->load->view('admin/allowance_types/manage', $data);
    }

    /**
     * Save (Add/Update) allowance type
     */
    public function save()
    {
        if (!$this->input->post()) {
            ajax_access_denied();
        }

        $data = $this->input->post();
        $id = isset($data['id']) && !empty($data['id']) ? $data['id'] : null;

        if ($id) {
            // Update
            $success = $this->allowance_types_model->update($id, $data);
            $message = $success ? _l('updated_successfully', _l('allowance_type')) : _l('problem_updating', _l('allowance_type'));
        } else {
            // Add
            $id = $this->allowance_types_model->add($data);
            $success = $id ? true : false;
            $message = $success ? _l('added_successfully', _l('allowance_type')) : _l('problem_adding', _l('allowance_type'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'id' => $id
        ]);
    }

    /**
     * Delete allowance type
     * @param int $id
     */
    public function delete($id)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $result = $this->allowance_types_model->delete($id);
        echo json_encode($result);
    }

    /**
     * Get all allowance types (AJAX)
     */
    public function get_all()
    {
        $allowances = $this->allowance_types_model->get();
        echo json_encode($allowances);
    }

    /**
     * Get single allowance type (AJAX)
     * @param int $id
     */
    public function get($id)
    {
        if (!is_numeric($id)) {
            ajax_access_denied();
        }

        $allowance = $this->allowance_types_model->get($id);
        echo json_encode($allowance);
    }

    /**
     * Toggle active status
     * @param int $id
     */
    public function toggle_active($id)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $success = $this->allowance_types_model->toggle_active($id);

        echo json_encode([
            'success' => $success,
            'message' => $success ? _l('updated_successfully', _l('allowance_type')) : _l('problem_updating', _l('allowance_type'))
        ]);
    }

    /**
     * Save assignments for an allowance type
     */
    public function save_assignment()
    {
        if (!$this->input->post()) {
            ajax_access_denied();
        }

        $data = $this->input->post();

        $assignment_data = [
            'allowance_type_id' => $data['allowance_type_id'],
            'employee_type'     => $data['employee_type'],
            'employee_type_id'  => $data['employee_type_id'],
            'is_mandatory'      => isset($data['is_mandatory']) ? 1 : 0,
            'default_amount'    => isset($data['default_amount']) && !empty($data['default_amount']) ? $data['default_amount'] : null,
        ];

        // Check if assignment already exists
        if ($this->allowance_assignments_model->assignment_exists(
            $assignment_data['allowance_type_id'],
            $assignment_data['employee_type'],
            $assignment_data['employee_type_id']
        )) {
            echo json_encode([
                'success' => false,
                'message' => _l('allowance_assignment_already_exists')
            ]);
            return;
        }

        $id = $this->allowance_assignments_model->add($assignment_data);

        echo json_encode([
            'success' => $id ? true : false,
            'message' => $id ? _l('added_successfully', _l('assignment')) : _l('problem_adding', _l('assignment')),
            'id' => $id
        ]);
    }

    /**
     * Get assignments for an allowance type
     * @param int $allowance_id
     */
    public function get_assignments($allowance_id)
    {
        if (!is_numeric($allowance_id)) {
            ajax_access_denied();
        }

        $assignments = $this->allowance_assignments_model->get_by_allowance_with_names($allowance_id);
        echo json_encode($assignments);
    }

    /**
     * Delete assignment
     * @param int $id
     */
    public function delete_assignment($id)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $success = $this->allowance_assignments_model->delete($id);

        echo json_encode([
            'success' => $success,
            'message' => $success ? _l('deleted', _l('assignment')) : _l('problem_deleting', _l('assignment'))
        ]);
    }

    /**
     * Get allowances for specific employee type (used in pay modal)
     * @param string $type 'company_type' or 'profession_type'
     * @param int $type_id
     */
    public function get_by_employee_type()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $type = $this->input->get('type');
        $type_id = $this->input->get('type_id');

        if (empty($type) || !is_numeric($type_id)) {
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }

        $allowances = $this->allowance_assignments_model->get_by_employee_type($type, $type_id);

        echo json_encode([
            'success' => true,
            'allowances' => $allowances
        ]);
    }
}
