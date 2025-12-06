<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotation extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipment_quotation_model');
        $this->load->model('equipment_rfq_model');
        $this->load->model('suppliers/suppliers_model');
    }

    /**
     * List all quotations
     */
    public function index()
    {
        if (!has_permission('equipment_quotation', '', 'view')) {
            access_denied('equipment_quotation');
        }

        $data['title'] = _l('supplier_quotations');
        $this->load->view('admin/quotation/index', $data);
    }

    /**
     * Get quotations for DataTables (AJAX)
     */
    public function table()
    {
        if (!has_permission('equipment_quotation', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/quotation/table'));
    }

    /**
     * Add new quotation
     */
    public function add($rfq_id = null)
    {
        if (!has_permission('equipment_quotation', '', 'create')) {
            access_denied('equipment_quotation');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Check if we should copy RFQ items
            $copy_rfq_items = isset($data['copy_rfq_items']) && $data['copy_rfq_items'] == '1';
            unset($data['copy_rfq_items']);

            $id = $this->equipment_quotation_model->add($data);

            if ($id) {
                // Copy RFQ items if requested
                if ($copy_rfq_items && isset($data['rfq_id']) && !empty($data['rfq_id'])) {
                    $this->equipment_quotation_model->copy_rfq_items_to_quotation($data['rfq_id'], $id);
                }

                set_alert('success', _l('added_successfully', _l('quotation')));
                redirect(admin_url('equipments/quotation/view/' . $id));
            } else {
                set_alert('danger', _l('added_fail', _l('quotation')));
            }
        }

        $data['title'] = _l('add_quotation');
        $data['rfqs'] = $this->equipment_rfq_model->get_all();
        $data['suppliers'] = $this->suppliers_model->get();

        // Pre-select RFQ if provided
        if ($rfq_id) {
            $data['selected_rfq'] = $rfq_id;
            $data['rfq'] = $this->equipment_rfq_model->get($rfq_id);
        }

        $this->load->view('admin/quotation/form', $data);
    }

    /**
     * Edit quotation
     */
    public function edit($id)
    {
        if (!has_permission('equipment_quotation', '', 'edit')) {
            access_denied('equipment_quotation');
        }

        $data['quotation'] = $this->equipment_quotation_model->get($id);

        if (!$data['quotation']) {
            show_404();
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();
            unset($post_data['copy_rfq_items']); // Not applicable on edit

            $success = $this->equipment_quotation_model->update($post_data, $id);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('quotation')));
                redirect(admin_url('equipments/quotation/view/' . $id));
            } else {
                set_alert('danger', _l('updated_fail', _l('quotation')));
            }
        }

        $data['title'] = _l('edit_quotation');
        $data['rfqs'] = $this->equipment_rfq_model->get_all();
        $data['suppliers'] = $this->suppliers_model->get();
        $this->load->view('admin/quotation/form', $data);
    }

    /**
     * View quotation details
     */
    public function view($id)
    {
        if (!has_permission('equipment_quotation', '', 'view')) {
            access_denied('equipment_quotation');
        }

        $data['quotation'] = $this->equipment_quotation_model->get($id);

        if (!$data['quotation']) {
            show_404();
        }

        $data['title'] = _l('supplier_quotation') . ' - ' . $data['quotation']->quotation_number;
        $this->load->view('admin/quotation/view', $data);
    }

    /**
     * Delete quotation
     */
    public function delete($id)
    {
        if (!has_permission('equipment_quotation', '', 'delete')) {
            ajax_access_denied();
        }

        $success = $this->equipment_quotation_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('quotation')));
        } else {
            set_alert('danger', _l('problem_deleting', _l('quotation')));
        }

        redirect(admin_url('equipments/quotation'));
    }

    // ========== Quotation Items Management ==========

    /**
     * Get quotation items (AJAX)
     */
    public function get_items($quotation_id)
    {
        $items = $this->equipment_quotation_model->get_quotation_items($quotation_id);

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Add quotation item
     */
    public function add_item()
    {
        if (!has_permission('equipment_quotation', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove item_id if empty (it's for edit operations only)
        if (isset($data['item_id']) && empty($data['item_id'])) {
            unset($data['item_id']);
        }

        $item_id = $this->equipment_quotation_model->add_quotation_item($data);

        if ($item_id) {
            echo json_encode(['success' => true, 'item_id' => $item_id, 'message' => _l('added_successfully', _l('quotation_item'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('quotation_item'))]);
        }
    }

    /**
     * Update quotation item
     */
    public function update_item($item_id)
    {
        if (!has_permission('equipment_quotation', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove item_id from data (it's passed as parameter)
        unset($data['item_id']);

        $success = $this->equipment_quotation_model->update_quotation_item($data, $item_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('quotation_item'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('quotation_item'))]);
        }
    }

    /**
     * Delete quotation item
     */
    public function delete_item($item_id)
    {
        if (!has_permission('equipment_quotation', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_quotation_model->delete_quotation_item($item_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('quotation_item'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('quotation_item'))]);
        }
    }

    // ========== Quotation Comparison & Actions ==========

    /**
     * Compare quotations for an RFQ
     */
    public function compare($rfq_id)
    {
        if (!has_permission('equipment_quotation', '', 'view')) {
            access_denied('equipment_quotation');
        }

        $data['rfq'] = $this->equipment_rfq_model->get($rfq_id);

        if (!$data['rfq']) {
            show_404();
        }

        $data['quotations'] = $this->equipment_quotation_model->get_quotations_for_comparison($rfq_id);
        $data['title'] = _l('compare_quotations') . ' - ' . $data['rfq']->rfq_number;

        $this->load->view('admin/quotation/compare', $data);
    }

    /**
     * Accept quotation (AJAX)
     */
    public function accept($quotation_id)
    {
        if (!has_permission('equipment_quotation', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_quotation_model->accept_quotation($quotation_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('quotation_accepted_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('quotation_accept_failed')]);
        }
    }

    /**
     * Recalculate quotation totals (AJAX)
     */
    public function recalculate_totals($quotation_id)
    {
        if (!has_permission('equipment_quotation', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_quotation_model->recalculate_totals($quotation_id);

        if ($success) {
            $quotation = $this->equipment_quotation_model->get($quotation_id);
            echo json_encode([
                'success' => true,
                'subtotal' => $quotation->subtotal,
                'tax_amount' => $quotation->tax_amount,
                'total_amount' => $quotation->total_amount,
                'message' => _l('totals_recalculated_successfully')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('totals_recalculation_failed')]);
        }
    }

    /**
     * Get RFQ items for populating quotation (AJAX)
     */
    public function get_rfq_items($rfq_id)
    {
        $items = $this->equipment_rfq_model->get_rfq_items($rfq_id);

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Convert accepted quotation to Purchase Order
     */
    public function convert_to_po($quotation_id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'create')) {
            access_denied('equipment_purchase_orders');
        }

        // Get quotation details
        $quotation = $this->equipment_quotation_model->get($quotation_id);

        if (!$quotation) {
            show_404();
        }

        // Check if quotation is accepted
        if ($quotation->status != 'accepted') {
            set_alert('danger', _l('quotation_must_be_accepted_first'));
            redirect(admin_url('equipments/quotation/view/' . $quotation_id));
            return;
        }

        // Redirect to PO add form with quotation_id parameter
        redirect(admin_url('equipments/purchase_orders/add?quotation_id=' . $quotation_id));
    }
}
