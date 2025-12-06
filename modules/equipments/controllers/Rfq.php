<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rfq extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipment_rfq_model');
        $this->load->model('suppliers/suppliers_model');
    }

    /**
     * List all RFQs
     */
    public function index()
    {
        if (!has_permission('equipment_rfq', '', 'view')) {
            access_denied('equipment_rfq');
        }

        $data['title'] = _l('rfq_list');
        $this->load->view('admin/rfq/index', $data);
    }

    /**
     * Get RFQs for DataTables (AJAX)
     */
    public function table()
    {
        if (!has_permission('equipment_rfq', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/rfq/table'));
    }

    /**
     * Add new RFQ
     */
    public function add()
    {
        if (!has_permission('equipment_rfq', '', 'create')) {
            access_denied('equipment_rfq');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->equipment_rfq_model->add($data);

            if ($id) {
                set_alert('success', _l('added_successfully', _l('rfq')));
                redirect(admin_url('equipments/rfq/view/' . $id));
            } else {
                set_alert('danger', _l('added_fail', _l('rfq')));
            }
        }

        $data['title'] = _l('add_rfq');
        $data['clients'] = $this->clients_model->get();
        $this->load->view('admin/rfq/form', $data);
    }

    /**
     * Edit RFQ
     */
    public function edit($id)
    {
        if (!has_permission('equipment_rfq', '', 'edit')) {
            access_denied('equipment_rfq');
        }

        $data['rfq'] = $this->equipment_rfq_model->get($id);

        if (!$data['rfq']) {
            show_404();
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();
            $success = $this->equipment_rfq_model->update($post_data, $id);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('rfq')));
                redirect(admin_url('equipments/rfq/view/' . $id));
            } else {
                set_alert('danger', _l('updated_fail', _l('rfq')));
            }
        }

        $data['title'] = _l('edit_rfq');
        $data['clients'] = $this->clients_model->get();
        $this->load->view('admin/rfq/form', $data);
    }

    /**
     * View RFQ details
     */
    public function view($id)
    {
        if (!has_permission('equipment_rfq', '', 'view')) {
            access_denied('equipment_rfq');
        }

        $data['rfq'] = $this->equipment_rfq_model->get($id);

        if (!$data['rfq']) {
            show_404();
        }

        $data['title'] = _l('rfq') . ' - ' . $data['rfq']->rfq_number;
        $this->load->view('admin/rfq/view', $data);
    }

    /**
     * Delete RFQ
     */
    public function delete($id)
    {
        if (!has_permission('equipment_rfq', '', 'delete')) {
            ajax_access_denied();
        }

        $success = $this->equipment_rfq_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('rfq')));
        } else {
            set_alert('danger', _l('problem_deleting', _l('rfq')));
        }

        redirect(admin_url('equipments/rfq'));
    }

    // ========== RFQ Items Management ==========

    /**
     * Get RFQ items (AJAX)
     */
    public function get_items($rfq_id)
    {
        $items = $this->equipment_rfq_model->get_rfq_items($rfq_id);

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Add RFQ item
     */
    public function add_item()
    {
        if (!has_permission('equipment_rfq', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove item_id if empty (it's for edit operations only)
        if (isset($data['item_id']) && empty($data['item_id'])) {
            unset($data['item_id']);
        }

        // Convert empty string to NULL for foreign key fields
        if (isset($data['equipment_id']) && $data['equipment_id'] === '') {
            $data['equipment_id'] = null;
        }
        if (isset($data['operator_id']) && $data['operator_id'] === '') {
            $data['operator_id'] = null;
        }

        $item_id = $this->equipment_rfq_model->add_rfq_item($data);

        if ($item_id) {
            echo json_encode(['success' => true, 'item_id' => $item_id, 'message' => _l('added_successfully', _l('rfq_item'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('rfq_item'))]);
        }
    }

    /**
     * Update RFQ item
     */
    public function update_item($item_id)
    {
        if (!has_permission('equipment_rfq', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove item_id from data (it's passed as parameter)
        unset($data['item_id']);

        // Convert empty string to NULL for foreign key fields
        if (isset($data['equipment_id']) && $data['equipment_id'] === '') {
            $data['equipment_id'] = null;
        }
        if (isset($data['operator_id']) && $data['operator_id'] === '') {
            $data['operator_id'] = null;
        }

        $success = $this->equipment_rfq_model->update_rfq_item($data, $item_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('rfq_item'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('rfq_item'))]);
        }
    }

    /**
     * Delete RFQ item
     */
    public function delete_item($item_id)
    {
        if (!has_permission('equipment_rfq', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_rfq_model->delete_rfq_item($item_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('rfq_item'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('rfq_item'))]);
        }
    }

    // ========== RFQ Suppliers Management ==========

    /**
     * Get RFQ suppliers (AJAX)
     */
    public function get_suppliers($rfq_id)
    {
        $suppliers = $this->equipment_rfq_model->get_rfq_suppliers($rfq_id);

        echo json_encode([
            'success' => true,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Add supplier to RFQ
     */
    public function add_supplier()
    {
        if (!has_permission('equipment_rfq', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove supplier_rfq_id if empty
        if (isset($data['supplier_rfq_id']) && empty($data['supplier_rfq_id'])) {
            unset($data['supplier_rfq_id']);
        }

        $id = $this->equipment_rfq_model->add_rfq_supplier($data);

        if ($id) {
            echo json_encode(['success' => true, 'id' => $id, 'message' => _l('added_successfully', _l('supplier'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('supplier'))]);
        }
    }

    /**
     * Update RFQ supplier
     */
    public function update_supplier($id)
    {
        if (!has_permission('equipment_rfq', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();
        unset($data['supplier_rfq_id']);

        $success = $this->equipment_rfq_model->update_rfq_supplier($data, $id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('supplier'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('supplier'))]);
        }
    }

    /**
     * Delete RFQ supplier
     */
    public function delete_supplier($id)
    {
        if (!has_permission('equipment_rfq', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_rfq_model->delete_rfq_supplier($id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('supplier'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('supplier'))]);
        }
    }

    /**
     * Get quotations for this RFQ (AJAX)
     */
    public function get_quotations($rfq_id)
    {
        if (!has_permission('equipment_quotation', '', 'view') && !has_permission('equipment_rfq', '', 'view')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $this->load->model('equipment_quotation_model');
        $quotations = $this->equipment_quotation_model->get_all($rfq_id);

        echo json_encode([
            'success' => true,
            'quotations' => $quotations
        ]);
    }
}
