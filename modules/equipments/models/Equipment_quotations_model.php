<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipment_quotations_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ========== Quotation CRUD ==========

    /**
     * Get quotation(s)
     * @param int $id Quotation ID (optional)
     * @return object|array Single quotation object or array of quotations
     */
    public function get($id = null)
    {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'equipment_client_quotations')->row();
        }

        return $this->db->get(db_prefix() . 'equipment_client_quotations')->result();
    }

    /**
     * Add new quotation
     * @param array $data Quotation data
     * @return int|bool Quotation ID on success, false on failure
     */
    public function add($data)
    {
        // Set NULL for empty foreign key fields to avoid constraint errors
        if (empty($data['agreement_id'])) {
            $data['agreement_id'] = null;
        }

        // Set NULL for empty validity_date
        if (empty($data['validity_date'])) {
            $data['validity_date'] = null;
        }

        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'equipment_client_quotations', $data);

        if ($this->db->affected_rows() > 0) {
            $insert_id = $this->db->insert_id();

            log_activity('New Quotation Created [ID: ' . $insert_id . ', Number: ' . $data['quotation_number'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update quotation
     * @param array $data Quotation data
     * @param int $id Quotation ID
     * @return bool True on success, false on failure
     */
    public function update($data, $id)
    {
        // Set NULL for empty foreign key fields to avoid constraint errors
        if (isset($data['agreement_id']) && empty($data['agreement_id'])) {
            $data['agreement_id'] = null;
        }

        // Set NULL for empty validity_date
        if (isset($data['validity_date']) && empty($data['validity_date'])) {
            $data['validity_date'] = null;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_client_quotations', $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Quotation Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete quotation (cascade deletes items, tiers, and charges)
     * @param int $id Quotation ID
     * @return bool True on success, false on failure
     */
    public function delete($id)
    {
        $quotation = $this->get($id);

        if (!$quotation) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'equipment_client_quotations');

        if ($this->db->affected_rows() > 0) {
            log_activity('Quotation Deleted [ID: ' . $id . ', Number: ' . $quotation->quotation_number . ']');
            return true;
        }

        return false;
    }

    /**
     * Generate unique quotation number
     * Format: QT-YYYY-XXX (e.g., QT-2025-001)
     * @return string Quotation number
     */
    public function generate_quotation_number()
    {
        $year = date('Y');
        $prefix = 'QT-' . $year . '-';

        // Get the latest quotation number for this year
        $this->db->select('quotation_number');
        $this->db->like('quotation_number', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $result = $this->db->get(db_prefix() . 'equipment_client_quotations')->row();

        if ($result) {
            // Extract the number part and increment
            $last_number = (int) substr($result->quotation_number, -3);
            $next_number = $last_number + 1;
        } else {
            $next_number = 1;
        }

        return $prefix . str_pad($next_number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Change quotation status
     * @param int $id Quotation ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function change_status($id, $status)
    {
        $allowed_statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired'];

        if (!in_array($status, $allowed_statuses)) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_client_quotations', ['status' => $status]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Quotation Status Changed [ID: ' . $id . ', Status: ' . $status . ']');
            return true;
        }

        return false;
    }

    // ========== Quotation Items CRUD ==========

    /**
     * Get quotation items
     * @param int $quotation_id Quotation ID
     * @return array Array of quotation items
     */
    public function get_quotation_items($quotation_id)
    {
        $this->db->where('quotation_id', $quotation_id);
        $this->db->order_by('id', 'ASC');
        $items = $this->db->get(db_prefix() . 'equipment_client_quotation_items')->result_array();

        // For each item, get equipment details if equipment_id is set
        foreach ($items as &$item) {
            if ($item['equipment_id']) {
                $equipment = $this->db->where('id', $item['equipment_id'])
                    ->get(db_prefix() . 'equipments')->row();
                if ($equipment) {
                    $item['equipment_name'] = $equipment->name;
                    $item['equipment_plate'] = $equipment->platenumber_code;
                }
            }
        }

        return $items;
    }

    /**
     * Add quotation item
     * @param array $data Item data
     * @return int|bool Item ID on success, false on failure
     */
    public function add_quotation_item($data)
    {
        // Set NULL for optional foreign key field
        if (isset($data['equipment_id']) && empty($data['equipment_id'])) {
            $data['equipment_id'] = null;
        }

        $this->db->insert(db_prefix() . 'equipment_client_quotation_items', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Update quotation item
     * @param array $data Item data
     * @param int $item_id Item ID
     * @return bool True on success, false on failure
     */
    public function update_quotation_item($data, $item_id)
    {
        // Set NULL for optional foreign key field
        if (isset($data['equipment_id']) && empty($data['equipment_id'])) {
            $data['equipment_id'] = null;
        }

        $this->db->where('id', $item_id);
        $this->db->update(db_prefix() . 'equipment_client_quotation_items', $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete quotation item (cascade deletes pricing tiers)
     * @param int $item_id Item ID
     * @return bool True on success, false on failure
     */
    public function delete_quotation_item($item_id)
    {
        $this->db->where('id', $item_id);
        $this->db->delete(db_prefix() . 'equipment_client_quotation_items');

        return $this->db->affected_rows() > 0;
    }

    // ========== Pricing Tiers CRUD ==========

    /**
     * Get pricing tiers for a quotation item
     * @param int $quotation_item_id Quotation item ID
     * @return array Array of pricing tiers
     */
    public function get_pricing_tiers($quotation_item_id)
    {
        $this->db->where('quotation_item_id', $quotation_item_id);
        $this->db->order_by('from_month', 'ASC');
        return $this->db->get(db_prefix() . 'equipment_client_quotation_pricing_tiers')->result_array();
    }

    /**
     * Get applicable pricing tier for a specific month
     * @param int $quotation_item_id Quotation item ID
     * @param int $month_number Month number (1, 2, 3, etc.)
     * @return object|null Pricing tier object or null if not found
     */
    public function get_pricing_tier_for_month($quotation_item_id, $month_number)
    {
        $this->db->where('quotation_item_id', $quotation_item_id);
        $this->db->where('from_month <=', $month_number);
        $this->db->group_start();
        $this->db->where('to_month >=', $month_number);
        $this->db->or_where('to_month IS NULL');
        $this->db->group_end();
        $this->db->order_by('from_month', 'DESC');
        $this->db->limit(1);

        return $this->db->get(db_prefix() . 'equipment_client_quotation_pricing_tiers')->row();
    }

    /**
     * Add pricing tier
     * @param array $data Tier data
     * @return int|bool Tier ID on success, false on failure
     */
    public function add_pricing_tier($data)
    {
        // Convert empty to_month to NULL
        if (isset($data['to_month']) && $data['to_month'] === '') {
            $data['to_month'] = null;
        }

        $this->db->insert(db_prefix() . 'equipment_client_quotation_pricing_tiers', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Update pricing tier
     * @param array $data Tier data
     * @param int $tier_id Tier ID
     * @return bool True on success, false on failure
     */
    public function update_pricing_tier($data, $tier_id)
    {
        // Convert empty to_month to NULL
        if (isset($data['to_month']) && $data['to_month'] === '') {
            $data['to_month'] = null;
        }

        $this->db->where('id', $tier_id);
        $this->db->update(db_prefix() . 'equipment_client_quotation_pricing_tiers', $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete pricing tier
     * @param int $tier_id Tier ID
     * @return bool True on success, false on failure
     */
    public function delete_pricing_tier($tier_id)
    {
        $this->db->where('id', $tier_id);
        $this->db->delete(db_prefix() . 'equipment_client_quotation_pricing_tiers');

        return $this->db->affected_rows() > 0;
    }

    // ========== Charges CRUD ==========

    /**
     * Get quotation charges
     * @param int $quotation_id Quotation ID
     * @return array Array of charges
     */
    public function get_quotation_charges($quotation_id)
    {
        $this->db->where('quotation_id', $quotation_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get(db_prefix() . 'equipment_client_quotation_charges')->result_array();
    }

    /**
     * Add quotation charge
     * @param array $data Charge data
     * @return int|bool Charge ID on success, false on failure
     */
    public function add_quotation_charge($data)
    {
        $this->db->insert(db_prefix() . 'equipment_client_quotation_charges', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Update quotation charge
     * @param array $data Charge data
     * @param int $charge_id Charge ID
     * @return bool True on success, false on failure
     */
    public function update_quotation_charge($data, $charge_id)
    {
        $this->db->where('id', $charge_id);
        $this->db->update(db_prefix() . 'equipment_client_quotation_charges', $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete quotation charge
     * @param int $charge_id Charge ID
     * @return bool True on success, false on failure
     */
    public function delete_quotation_charge($charge_id)
    {
        $this->db->where('id', $charge_id);
        $this->db->delete(db_prefix() . 'equipment_client_quotation_charges');

        return $this->db->affected_rows() > 0;
    }

    // ========== Utility Functions ==========

    /**
     * Calculate total quotation value
     * @param int $quotation_id Quotation ID
     * @param int $months Number of months to calculate (default: 12)
     * @return array Array with breakdown of costs
     */
    public function calculate_quotation_total($quotation_id, $months = 12)
    {
        $items = $this->get_quotation_items($quotation_id);
        $charges = $this->get_quotation_charges($quotation_id);

        $monthly_costs = [];
        $total_equipment = 0;

        // Calculate monthly equipment costs
        foreach ($items as $item) {
            $tiers = $this->get_pricing_tiers($item['id']);

            for ($month = 1; $month <= $months; $month++) {
                $tier = $this->get_pricing_tier_for_month($item['id'], $month);

                if ($tier) {
                    $monthly_rate = $tier->monthly_rate * $item['quantity'];
                    $monthly_costs[$month] = ($monthly_costs[$month] ?? 0) + $monthly_rate;
                    $total_equipment += $monthly_rate;
                }
            }
        }

        // Calculate total charges
        $total_charges = 0;
        foreach ($charges as $charge) {
            if ($charge['status'] != 'waived' && $charge['status'] != 'cancelled') {
                $total_charges += $charge['amount'];
            }
        }

        $grand_total = $total_equipment + $total_charges;

        return [
            'monthly_costs' => $monthly_costs,
            'total_equipment' => $total_equipment,
            'total_charges' => $total_charges,
            'grand_total' => $grand_total,
            'months_calculated' => $months,
        ];
    }
}
