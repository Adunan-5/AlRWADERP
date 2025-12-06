<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotations extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('equipments/equipment_quotations_model');
        $this->load->model('equipments/equipments_model');
        $this->load->language('equipments/equipments');
    }

    /**
     * List all quotations
     */
    public function index()
    {
        if (!has_permission('equipment_quotations', '', 'view')) {
            access_denied('Equipment Quotations');
        }

        $data['title'] = _l('equipment_quotations');
        $this->load->view('admin/quotations/manage', $data);
    }

    /**
     * Get quotations for DataTables
     */
    public function table()
    {
        if (!has_permission('equipment_quotations', '', 'view')) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('equipments', 'admin/tables/quotations'));
    }

    /**
     * Add new quotation
     */
    public function add()
    {
        if (!has_permission('equipment_quotations', '', 'create')) {
            access_denied('Equipment Quotations');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Generate quotation number if not provided
            if (empty($data['quotation_number'])) {
                $data['quotation_number'] = $this->equipment_quotations_model->generate_quotation_number();
            }

            // Convert dates to SQL format
            if (!empty($data['quotation_date'])) {
                $data['quotation_date'] = to_sql_date($data['quotation_date']);
            }
            if (!empty($data['validity_date'])) {
                $data['validity_date'] = to_sql_date($data['validity_date']);
            }

            $quotation_id = $this->equipment_quotations_model->add($data);

            if ($quotation_id) {
                set_alert('success', _l('added_successfully', _l('quotation')));
                redirect(admin_url('equipments/quotations/view/' . $quotation_id));
            } else {
                set_alert('danger', _l('added_fail', _l('quotation')));
            }
        }

        // Prepare form data
        $data['title'] = _l('add_new_quotation');

        // Get clients
        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get();

        // Get client agreements only
        $this->load->model('equipments/equipment_agreements_model');
        $all_agreements = $this->equipment_agreements_model->get();
        $data['client_agreements'] = [];
        if ($all_agreements) {
            foreach ($all_agreements as $agreement) {
                $agreement_type = is_object($agreement) ? $agreement->agreement_type : $agreement['agreement_type'];
                if ($agreement_type === 'client') {
                    $data['client_agreements'][] = $agreement;
                }
            }
        }

        $this->load->view('admin/quotations/quotation', $data);
    }

    /**
     * View quotation details
     */
    public function view($id)
    {
        if (!has_permission('equipment_quotations', '', 'view')) {
            access_denied('Equipment Quotations');
        }

        $quotation = $this->equipment_quotations_model->get($id);

        if (!$quotation) {
            show_404();
        }

        $data['quotation'] = $quotation;
        $data['title'] = _l('quotation') . ' - ' . $quotation->quotation_number;

        // Get client details
        $this->load->model('clients_model');
        $data['client'] = $this->clients_model->get($quotation->client_id);

        // Get agreement details if linked
        if ($quotation->agreement_id) {
            $this->load->model('equipments/equipment_agreements_model');
            $data['agreement'] = $this->equipment_agreements_model->get($quotation->agreement_id);
        }

        $this->load->view('admin/quotations/view', $data);
    }

    /**
     * Edit quotation
     */
    public function edit($id)
    {
        if (!has_permission('equipment_quotations', '', 'edit')) {
            access_denied('Equipment Quotations');
        }

        $quotation = $this->equipment_quotations_model->get($id);

        if (!$quotation) {
            show_404();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Convert dates to SQL format
            if (!empty($data['quotation_date'])) {
                $data['quotation_date'] = to_sql_date($data['quotation_date']);
            }
            if (!empty($data['validity_date'])) {
                $data['validity_date'] = to_sql_date($data['validity_date']);
            }

            $success = $this->equipment_quotations_model->update($data, $id);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('quotation')));
            } else {
                set_alert('danger', _l('updated_fail', _l('quotation')));
            }

            redirect(admin_url('equipments/quotations/view/' . $id));
        }

        // Prepare form data
        $data['quotation'] = $quotation;
        $data['title'] = _l('edit_quotation') . ' - ' . $quotation->quotation_number;

        // Get clients
        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get();

        // Get client agreements only
        $this->load->model('equipments/equipment_agreements_model');
        $all_agreements = $this->equipment_agreements_model->get();
        $data['client_agreements'] = [];
        if ($all_agreements) {
            foreach ($all_agreements as $agreement) {
                $agreement_type = is_object($agreement) ? $agreement->agreement_type : $agreement['agreement_type'];
                if ($agreement_type === 'client') {
                    $data['client_agreements'][] = $agreement;
                }
            }
        }

        $this->load->view('admin/quotations/quotation', $data);
    }

    /**
     * Delete quotation
     */
    public function delete($id)
    {
        if (!has_permission('equipment_quotations', '', 'delete')) {
            ajax_access_denied();
        }

        $quotation = $this->equipment_quotations_model->get($id);

        if (!$quotation) {
            echo json_encode(['success' => false, 'message' => _l('quotation_not_found')]);
            return;
        }

        $success = $this->equipment_quotations_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('quotation')));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('quotation'))]);
        }
    }

    /**
     * Change quotation status
     */
    public function change_status($id, $status)
    {
        if (!has_permission('equipment_quotations', '', 'edit')) {
            ajax_access_denied();
        }

        $success = $this->equipment_quotations_model->change_status($id, $status);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('status_changed_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('something_went_wrong')]);
        }
    }

    // ========== Quotation Items Management ==========

    /**
     * Get quotation items (AJAX)
     */
    public function get_items($quotation_id)
    {
        $items = $this->equipment_quotations_model->get_quotation_items($quotation_id);

        // For each item, get its pricing tiers
        foreach ($items as &$item) {
            $item['tiers'] = $this->equipment_quotations_model->get_pricing_tiers($item['id']);
        }

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
        if (!has_permission('equipment_quotations', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove item_id if empty (it's for edit operations only)
        if (isset($data['item_id']) && empty($data['item_id'])) {
            unset($data['item_id']);
        }

        $item_id = $this->equipment_quotations_model->add_quotation_item($data);

        if ($item_id) {
            echo json_encode(['success' => true, 'item_id' => $item_id, 'message' => _l('added_successfully', _l('equipment'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('equipment'))]);
        }
    }

    /**
     * Update quotation item
     */
    public function update_item($item_id)
    {
        if (!has_permission('equipment_quotations', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove item_id from data (it's passed as parameter)
        unset($data['item_id']);

        $success = $this->equipment_quotations_model->update_quotation_item($data, $item_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('equipment'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('equipment'))]);
        }
    }

    /**
     * Delete quotation item
     */
    public function delete_item($item_id)
    {
        if (!has_permission('equipment_quotations', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_quotations_model->delete_quotation_item($item_id);

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
        if (!has_permission('equipment_quotations', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove tier_id if empty (it's for edit operations only)
        if (isset($data['tier_id']) && empty($data['tier_id'])) {
            unset($data['tier_id']);
        }

        $tier_id = $this->equipment_quotations_model->add_pricing_tier($data);

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
        if (!has_permission('equipment_quotations', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();
        unset($data['tier_id']); // Remove if present

        $success = $this->equipment_quotations_model->update_pricing_tier($data, $tier_id);

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
        if (!has_permission('equipment_quotations', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_quotations_model->delete_pricing_tier($tier_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('pricing_tier'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('pricing_tier'))]);
        }
    }

    // ========== Charges Management ==========

    /**
     * Get quotation charges (AJAX)
     */
    public function get_charges($quotation_id)
    {
        $charges = $this->equipment_quotations_model->get_quotation_charges($quotation_id);

        echo json_encode([
            'success' => true,
            'charges' => $charges
        ]);
    }

    /**
     * Add quotation charge
     */
    public function add_charge()
    {
        if (!has_permission('equipment_quotations', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove charge_id if empty (it's for edit operations only)
        if (isset($data['charge_id']) && empty($data['charge_id'])) {
            unset($data['charge_id']);
        }

        $charge_id = $this->equipment_quotations_model->add_quotation_charge($data);

        if ($charge_id) {
            echo json_encode(['success' => true, 'charge_id' => $charge_id, 'message' => _l('added_successfully', _l('charge'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('added_fail', _l('charge'))]);
        }
    }

    /**
     * Update quotation charge
     */
    public function update_charge($charge_id)
    {
        if (!has_permission('equipment_quotations', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $data = $this->input->post();

        // Remove charge_id from data (it's passed as parameter)
        unset($data['charge_id']);

        $success = $this->equipment_quotations_model->update_quotation_charge($data, $charge_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('charge'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('updated_fail', _l('charge'))]);
        }
    }

    /**
     * Delete quotation charge
     */
    public function delete_charge($charge_id)
    {
        if (!has_permission('equipment_quotations', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $success = $this->equipment_quotations_model->delete_quotation_charge($charge_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => _l('deleted', _l('charge'))]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('problem_deleting', _l('charge'))]);
        }
    }
}
