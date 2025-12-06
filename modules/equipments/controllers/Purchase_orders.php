<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_orders extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipments/equipment_purchase_orders_model');
        $this->load->model('equipments/equipments_model');
        $this->load->language('equipments/equipments');
    }

    /**
     * List all purchase orders
     */
    public function index()
    {
        if (!has_permission('equipment_purchase_orders', '', 'view')) {
            access_denied('Equipment Purchase Orders');
        }

        $data['title'] = _l('equipment_purchase_orders');
        $this->load->view('admin/purchase_orders/manage', $data);
    }

    /**
     * Get purchase orders for DataTables
     */
    public function table()
    {
        if (!has_permission('equipment_purchase_orders', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/tables/purchase_orders'));
    }

    /**
     * Debug endpoint to check raw response
     */
    public function debug_table()
    {
        if (!is_admin()) {
            die('Admin access only');
        }

        // Start output buffering to capture everything
        ob_start();

        include(module_views_path('equipments', 'admin/tables/purchase_orders'));

        $output = ob_get_clean();

        echo '<pre>';
        echo 'Output length: ' . strlen($output) . "\n\n";
        echo 'First 500 chars: ' . substr($output, 0, 500) . "\n\n";
        echo 'Is valid JSON: ' . (json_decode($output) !== null ? 'YES' : 'NO') . "\n\n";
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'JSON Error: ' . json_last_error_msg() . "\n\n";
        }
        echo 'Full output:' . "\n";
        echo htmlspecialchars($output);
        echo '</pre>';
    }

    /**
     * Add new purchase order
     */
    public function add()
    {
        if (!has_permission('equipment_purchase_orders', '', 'create')) {
            access_denied('Equipment Purchase Orders');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Generate PO number if not provided
            if (empty($data['po_number'])) {
                $data['po_number'] = $this->equipment_purchase_orders_model->generate_po_number();
            }

            // Convert dates to SQL format
            if (!empty($data['po_date'])) {
                $data['po_date'] = to_sql_date($data['po_date']);
            }
            if (!empty($data['delivery_date'])) {
                $data['delivery_date'] = to_sql_date($data['delivery_date']);
            }
            if (!empty($data['validity_date'])) {
                $data['validity_date'] = to_sql_date($data['validity_date']);
            }

            $po_id = $this->equipment_purchase_orders_model->add($data);

            if ($po_id) {
                // If this PO was converted from a quotation, copy quotation items to PO
                if (!empty($data['quotation_id'])) {
                    $this->load->model('equipment_quotation_model');
                    $this->equipment_purchase_orders_model->copy_quotation_items_to_po($data['quotation_id'], $po_id);
                }

                set_alert('success', _l('added_successfully', _l('purchase_order')));
                redirect(admin_url('equipments/purchase_orders/view/' . $po_id));
            } else {
                set_alert('danger', _l('added_fail', _l('purchase_order')));
            }
        }

        // Prepare form data
        $data['title'] = _l('add_new_purchase_order');

        // Check if converting from quotation
        $quotation_id = $this->input->get('quotation_id');
        if ($quotation_id) {
            $this->load->model('equipment_quotation_model');
            $quotation = $this->equipment_quotation_model->get($quotation_id);

            if ($quotation) {
                $data['source_quotation'] = $quotation;
                $data['quotation_items'] = $this->equipment_quotation_model->get_quotation_items($quotation_id);
            }
        }

        // Get suppliers
        $this->load->model('suppliers/suppliers_model');
        $data['suppliers'] = $this->suppliers_model->get();

        // Get supplier agreements only (optional linking)
        $this->load->model('equipments/equipment_agreements_model');
        $all_agreements = $this->equipment_agreements_model->get();
        $data['supplier_agreements'] = [];
        if ($all_agreements) {
            foreach ($all_agreements as $agreement) {
                // Handle both object and array format
                $agreement_type = is_object($agreement) ? $agreement->agreement_type : $agreement['agreement_type'];
                if ($agreement_type === 'supplier') {
                    $data['supplier_agreements'][] = $agreement;
                }
            }
        }

        $this->load->view('admin/purchase_orders/purchase_order', $data);
    }

    /**
     * View purchase order details
     */
    public function view($id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'view')) {
            access_denied('Equipment Purchase Orders');
        }

        $po = $this->equipment_purchase_orders_model->get($id);

        if (!$po) {
            show_404();
        }

        $data['po'] = $po;
        $data['title'] = _l('purchase_order') . ' - ' . $po->po_number;

        // Get supplier details
        $this->load->model('suppliers/suppliers_model');
        $data['supplier'] = $this->suppliers_model->get($po->supplier_id);

        // Get agreement details if linked
        if ($po->agreement_id) {
            $this->load->model('equipments/equipment_agreements_model');
            $data['agreement'] = $this->equipment_agreements_model->get($po->agreement_id);
        }

        // Get source quotation if this PO was converted from a quotation
        if (!empty($po->quotation_id)) {
            $this->load->model('equipment_quotation_model');
            $data['source_quotation'] = $this->equipment_quotation_model->get($po->quotation_id);
        }

        $this->load->view('admin/purchase_orders/view', $data);
    }

    /**
     * Edit purchase order
     */
    public function edit($id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
            access_denied('Equipment Purchase Orders');
        }

        $po = $this->equipment_purchase_orders_model->get($id);

        if (!$po) {
            show_404();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Convert dates to SQL format
            if (!empty($data['po_date'])) {
                $data['po_date'] = to_sql_date($data['po_date']);
            }
            if (!empty($data['delivery_date'])) {
                $data['delivery_date'] = to_sql_date($data['delivery_date']);
            }
            if (!empty($data['validity_date'])) {
                $data['validity_date'] = to_sql_date($data['validity_date']);
            }

            $success = $this->equipment_purchase_orders_model->update($data, $id);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('purchase_order')));
            } else {
                set_alert('danger', _l('updated_fail', _l('purchase_order')));
            }

            redirect(admin_url('equipments/purchase_orders/view/' . $id));
        }

        // Prepare form data
        $data['po'] = $po;
        $data['title'] = _l('edit_purchase_order') . ' - ' . $po->po_number;

        // Get suppliers
        $this->load->model('suppliers/suppliers_model');
        $data['suppliers'] = $this->suppliers_model->get();

        // Get supplier agreements only
        $this->load->model('equipments/equipment_agreements_model');
        $all_agreements = $this->equipment_agreements_model->get();
        $data['supplier_agreements'] = [];
        if ($all_agreements) {
            foreach ($all_agreements as $agreement) {
                // Handle both object and array format
                $agreement_type = is_object($agreement) ? $agreement->agreement_type : $agreement['agreement_type'];
                if ($agreement_type === 'supplier') {
                    $data['supplier_agreements'][] = $agreement;
                }
            }
        }

        $this->load->view('admin/purchase_orders/purchase_order', $data);
    }

    /**
     * Delete purchase order
     */
    public function delete($id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'delete')) {
            ajax_access_denied();
        }

        $po = $this->equipment_purchase_orders_model->get($id);

        if (!$po) {
            echo json_encode(['success' => false, 'message' => _l('purchase_order_not_found')]);
            return;
        }

        $success = $this->equipment_purchase_orders_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('purchase_order')));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('purchase_order'))]);
        }
    }

    /**
     * Change PO status
     */
    public function change_status($id, $status)
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
            ajax_access_denied();
        }

        $success = $this->equipment_purchase_orders_model->change_status($id, $status);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('status_changed_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('something_went_wrong')]);
        }
    }

    // ========== PO Items Management ==========

    /**
     * Get PO items (AJAX)
     */
    public function get_items($po_id)
    {
        $items = $this->equipment_purchase_orders_model->get_po_items($po_id);

        // For each item, get its pricing tiers
        foreach ($items as &$item) {
            $item['tiers'] = $this->equipment_purchase_orders_model->get_pricing_tiers($item['id']);
        }

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Add PO item
     */
    public function add_item()
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
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

        $item_id = $this->equipment_purchase_orders_model->add_po_item($data);

        if ($item_id) {
            echo json_encode(['success' => true, 'item_id' => $item_id, 'message' => _l('added_successfully', _l('equipment'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('equipment'))]);
        }
    }

    /**
     * Update PO item
     */
    public function update_item($item_id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
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

        $success = $this->equipment_purchase_orders_model->update_po_item($data, $item_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('equipment'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('equipment'))]);
        }
    }

    /**
     * Delete PO item
     */
    public function delete_item($item_id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_purchase_orders_model->delete_po_item($item_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('equipment'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('equipment'))]);
        }
    }

    // ========== Pricing Tiers Management ==========

    /**
     * Add pricing tier
     */
    public function add_tier()
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove tier_id if empty (it's for edit operations only)
        if (isset($data['tier_id']) && empty($data['tier_id'])) {
            unset($data['tier_id']);
        }

        $tier_id = $this->equipment_purchase_orders_model->add_pricing_tier($data);

        if ($tier_id) {
            echo json_encode(['success' => true, 'tier_id' => $tier_id, 'message' => _l('added_successfully', _l('pricing_tier'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('pricing_tier'))]);
        }
    }

    /**
     * Update pricing tier
     */
    public function update_tier($tier_id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();
        unset($data['tier_id']); // Remove if present

        $success = $this->equipment_purchase_orders_model->update_pricing_tier($data, $tier_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('pricing_tier'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('pricing_tier'))]);
        }
    }

    /**
     * Delete pricing tier
     */
    public function delete_tier($tier_id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_purchase_orders_model->delete_pricing_tier($tier_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('pricing_tier'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('pricing_tier'))]);
        }
    }

    // ========== Charges Management ==========

    /**
     * Get PO charges (AJAX)
     */
    public function get_charges($po_id)
    {
        $charges = $this->equipment_purchase_orders_model->get_po_charges($po_id);

        echo json_encode([
            'success' => true,
            'charges' => $charges
        ]);
    }

    /**
     * Add PO charge
     */
    public function add_charge()
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove charge_id if empty (it's for edit operations only)
        if (isset($data['charge_id']) && empty($data['charge_id'])) {
            unset($data['charge_id']);
        }

        // Convert charge_date to SQL format
        if (!empty($data['charge_date'])) {
            $data['charge_date'] = to_sql_date($data['charge_date']);
        }

        $charge_id = $this->equipment_purchase_orders_model->add_po_charge($data);

        if ($charge_id) {
            echo json_encode(['success' => true, 'charge_id' => $charge_id, 'message' => _l('added_successfully', _l('charge'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('charge'))]);
        }
    }

    /**
     * Update PO charge
     */
    public function update_charge($charge_id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove charge_id from data (it's passed as parameter)
        unset($data['charge_id']);

        // Convert charge_date to SQL format
        if (!empty($data['charge_date'])) {
            $data['charge_date'] = to_sql_date($data['charge_date']);
        }

        $success = $this->equipment_purchase_orders_model->update_po_charge($data, $charge_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('charge'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('charge'))]);
        }
    }

    /**
     * Delete PO charge
     */
    public function delete_charge($charge_id)
    {
        if (!has_permission('equipment_purchase_orders', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_purchase_orders_model->delete_po_charge($charge_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('charge'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('charge'))]);
        }
    }
}
