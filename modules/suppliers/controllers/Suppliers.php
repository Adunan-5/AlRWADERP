<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('suppliers_model');
        $this->load->language('suppliers/suppliers');
    }

    public function index()
    {

        if (!has_permission('suppliers', '', 'view')) {
            access_denied('Suppliers');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('suppliers'); // We'll make this in the next step
        }

        $data['title'] = _l('suppliers');
        $this->load->view('admin/manage', $data);
    }

    public function add()
    {

        if (!has_permission('suppliers', '', 'create')) {
            access_denied('Suppliers');
        }
        if ($this->input->post()) {
            $data = $this->input->post();

            // Explicitly handle checkboxes:
            $data['split_timesheet_based_on_invoice_period'] = $this->input->post('split_timesheet_based_on_invoice_period') ? 1 : 0;
            $data['split_timesheet_based_on_project'] = $this->input->post('split_timesheet_based_on_project') ? 1 : 0;
            $data['enable_vat'] = $this->input->post('enable_vat') ? 1 : 0;
            $data['po_rate_include_vat'] = $this->input->post('po_rate_include_vat') ? 1 : 0;

            $this->suppliers_model->add($data);
            set_alert('success', 'Supplier added successfully');
            redirect(admin_url('suppliers'));
        }

        $data['title'] = _l('add_new_supplier');
        $this->load->view('admin/suppliers', $data);
    }


    public function edit($id)
    {

        if (!has_permission('suppliers', '', 'edit')) {
            access_denied('Suppliers');
        }
        $supplier = $this->suppliers_model->get($id);
        if (!$supplier) {
            show_404();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Explicitly handle checkboxes:
            $data['split_timesheet_based_on_invoice_period'] = $this->input->post('split_timesheet_based_on_invoice_period') ? 1 : 0;
            $data['split_timesheet_based_on_project'] = $this->input->post('split_timesheet_based_on_project') ? 1 : 0;
            $data['enable_vat'] = $this->input->post('enable_vat') ? 1 : 0;
            $data['po_rate_include_vat'] = $this->input->post('po_rate_include_vat') ? 1 : 0;

            $this->suppliers_model->update($data, $id);
            set_alert('success', 'Supplier updated successfully');
            redirect(admin_url('suppliers'));
        }

        // ✅ Load employees linked to this supplier
        $data['employees'] = $this->suppliers_model->getSupplierEmployees($id);

        $data['supplier'] = $supplier;
        $data['title'] = _l('edit') . ' ' . $supplier->name;
        $this->load->view('admin/suppliers', $data);
    }


    public function delete($id)
    {

        if (!has_permission('suppliers', '', 'delete')) {
            access_denied('Suppliers');
        }
        $this->suppliers_model->delete($id);
        set_alert('success', 'Supplier deleted');
        redirect(admin_url('suppliers'));
    }
}
