<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Agreements extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipments/equipment_agreements_model');
        $this->load->model('equipments/equipments_model');
        $this->load->language('equipments/equipments');
    }

    /**
     * List all agreements
     */
    public function index()
    {
        if (!has_permission('equipment_agreements', '', 'view')) {
            access_denied('Equipment Agreements');
        }

        $data['title'] = _l('equipment_agreements');
        $this->load->view('admin/agreements/manage', $data);
    }

    /**
     * Get agreements for DataTables
     */
    public function table()
    {
        if (!has_permission('equipment_agreements', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/tables/agreements'));
    }

    /**
     * Add new agreement
     */
    public function add()
    {
        if (!has_permission('equipment_agreements', '', 'create')) {
            access_denied('Equipment Agreements');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Set party_id based on agreement_type
            if ($data['agreement_type'] === 'supplier') {
                $data['party_id'] = $data['supplier_id'];
            } else {
                $data['party_id'] = $data['client_id'];
            }

            // Remove supplier_id and client_id from data
            unset($data['supplier_id']);
            unset($data['client_id']);

            // Generate agreement number if not provided
            if (empty($data['agreement_number'])) {
                $data['agreement_number'] = $this->equipment_agreements_model->generate_agreement_number($data['agreement_type']);
            }

            // Convert dates to SQL format
            if (!empty($data['start_date'])) {
                $data['start_date'] = to_sql_date($data['start_date']);
            }
            if (!empty($data['end_date'])) {
                $data['end_date'] = to_sql_date($data['end_date']);
            }
            if (!empty($data['signed_date'])) {
                $data['signed_date'] = to_sql_date($data['signed_date']);
            }

            $agreement_id = $this->equipment_agreements_model->add($data);

            if ($agreement_id) {
                set_alert('success', _l('added_successfully', _l('agreement')));
                redirect(admin_url('equipments/agreements/view/' . $agreement_id));
            } else {
                set_alert('danger', _l('added_fail', _l('agreement')));
            }
        }

        // Prepare form data
        $data['title'] = _l('add_new_agreement');
        $data['agreement_types'] = [
            ['value' => 'supplier', 'label' => _l('supplier_agreement')],
            ['value' => 'client', 'label' => _l('client_agreement')]
        ];

        // Get suppliers and clients for dropdowns
        $this->load->model('suppliers/suppliers_model');
        $this->load->model('clients_model');
        $data['suppliers'] = $this->suppliers_model->get();
        $data['clients'] = $this->clients_model->get();

        // Get projects for client agreements
        $this->load->model('projects_model');
        $data['projects'] = $this->projects_model->get();

        $this->load->view('admin/agreements/agreement', $data);
    }

    /**
     * View agreement details
     */
    public function view($id)
    {
        if (!has_permission('equipment_agreements', '', 'view')) {
            access_denied('Equipment Agreements');
        }

        $agreement = $this->equipment_agreements_model->get($id);

        if (!$agreement) {
            show_404();
        }

        $data['agreement'] = $agreement;
        $data['title'] = _l('agreement') . ' - ' . $agreement->agreement_number;

        // Get party details (supplier or client)
        if ($agreement->agreement_type === 'supplier') {
            $this->load->model('suppliers/suppliers_model');
            $data['party'] = $this->suppliers_model->get($agreement->party_id);
            $data['party_type'] = 'supplier';
        } else {
            $this->load->model('clients_model');
            $data['party'] = $this->clients_model->get($agreement->party_id);
            $data['party_type'] = 'client';

            // Get project if linked
            if ($agreement->project_id) {
                $this->load->model('projects_model');
                $data['project'] = $this->projects_model->get($agreement->project_id);
            }
        }

        $this->load->view('admin/agreements/view', $data);
    }

    /**
     * Edit agreement
     */
    public function edit($id)
    {
        if (!has_permission('equipment_agreements', '', 'edit')) {
            access_denied('Equipment Agreements');
        }

        $agreement = $this->equipment_agreements_model->get($id);

        if (!$agreement) {
            show_404();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Set party_id based on agreement_type
            if ($data['agreement_type'] === 'supplier') {
                $data['party_id'] = $data['supplier_id'];
            } else {
                $data['party_id'] = $data['client_id'];
            }

            // Remove supplier_id and client_id from data
            unset($data['supplier_id']);
            unset($data['client_id']);

            // Convert dates to SQL format
            if (!empty($data['start_date'])) {
                $data['start_date'] = to_sql_date($data['start_date']);
            }
            if (!empty($data['end_date'])) {
                $data['end_date'] = to_sql_date($data['end_date']);
            }
            if (!empty($data['signed_date'])) {
                $data['signed_date'] = to_sql_date($data['signed_date']);
            }

            $success = $this->equipment_agreements_model->update($data, $id);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('agreement')));
            } else {
                set_alert('danger', _l('updated_fail', _l('agreement')));
            }

            redirect(admin_url('equipments/agreements/view/' . $id));
        }

        // Prepare form data
        $data['agreement'] = $agreement;
        $data['title'] = _l('edit_agreement') . ' - ' . $agreement->agreement_number;
        $data['agreement_types'] = [
            ['value' => 'supplier', 'label' => _l('supplier_agreement')],
            ['value' => 'client', 'label' => _l('client_agreement')]
        ];

        // Get suppliers and clients for dropdowns
        $this->load->model('suppliers/suppliers_model');
        $this->load->model('clients_model');
        $data['suppliers'] = $this->suppliers_model->get();
        $data['clients'] = $this->clients_model->get();

        // Get projects for client agreements
        $this->load->model('projects_model');
        $data['projects'] = $this->projects_model->get();

        $this->load->view('admin/agreements/agreement', $data);
    }

    /**
     * Delete agreement
     */
    public function delete($id)
    {
        if (!has_permission('equipment_agreements', '', 'delete')) {
            ajax_access_denied();
        }

        $agreement = $this->equipment_agreements_model->get($id);

        if (!$agreement) {
            echo json_encode(['success' => false, 'message' => _l('agreement_not_found')]);
            return;
        }

        $success = $this->equipment_agreements_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('agreement')));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('agreement'))]);
        }
    }
}
