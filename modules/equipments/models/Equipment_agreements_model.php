<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipment_agreements_model extends App_Model
{
    protected $table = 'tblequipment_agreements';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get agreement(s)
     * @param mixed $id Agreement ID or empty for all
     * @return mixed Single agreement object or array of agreements
     */
    public function get($id = '')
    {
        if ($id != '') {
            $this->db->where('id', $id);
            return $this->db->get($this->table)->row();
        }
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Add new agreement
     * @param array $data Agreement data
     * @return int Agreement ID
     */
    public function add($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update agreement
     * @param array $data Agreement data
     * @param int $id Agreement ID
     * @return bool Success status
     */
    public function update($data, $id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Delete agreement and all related data (cascaded by DB)
     * @param int $id Agreement ID
     * @return int Affected rows
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    /**
     * Get agreements by type
     * @param string $type 'supplier' or 'client'
     * @return array Agreements
     */
    public function get_by_type($type)
    {
        $this->db->where('agreement_type', $type);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Get active agreements
     * @param string $type Optional filter by type
     * @return array Active agreements
     */
    public function get_active($type = null)
    {
        $this->db->where('status', 'active');
        if ($type) {
            $this->db->where('agreement_type', $type);
        }
        $this->db->order_by('start_date', 'DESC');
        return $this->db->get($this->table)->result_array();
    }

    // ========== Agreement Items Methods ==========

    /**
     * Get agreement items
     * @param int $agreement_id Agreement ID
     * @return array Agreement items with equipment details
     */
    public function get_agreement_items($agreement_id)
    {
        $this->db->select('ai.*, e.name as equipment_name, e.platenumber_code, e.equipmenttype');
        $this->db->from(db_prefix() . 'equipment_agreement_items ai');
        $this->db->join(db_prefix() . 'equipments e', 'e.id = ai.equipment_id', 'left');
        $this->db->where('ai.agreement_id', $agreement_id);
        $this->db->order_by('ai.id', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Get single agreement item
     * @param int $item_id Item ID
     * @return object Agreement item
     */
    public function get_agreement_item($item_id)
    {
        $this->db->where('id', $item_id);
        return $this->db->get(db_prefix() . 'equipment_agreement_items')->row();
    }

    /**
     * Add agreement item
     * @param array $data Item data
     * @return int Item ID
     */
    public function add_agreement_item($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'equipment_agreement_items', $data);
        return $this->db->insert_id();
    }

    /**
     * Update agreement item
     * @param array $data Item data
     * @param int $item_id Item ID
     * @return bool Success status
     */
    public function update_agreement_item($data, $item_id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $item_id);
        $this->db->update(db_prefix() . 'equipment_agreement_items', $data);
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Delete agreement item (will cascade delete pricing tiers)
     * @param int $item_id Item ID
     * @return int Affected rows
     */
    public function delete_agreement_item($item_id)
    {
        $this->db->where('id', $item_id);
        $this->db->delete(db_prefix() . 'equipment_agreement_items');
        return $this->db->affected_rows();
    }

    // ========== Pricing Tiers Methods ==========

    /**
     * Get pricing tiers for an agreement item
     * @param int $agreement_item_id Agreement item ID
     * @return array Pricing tiers ordered by from_month
     */
    public function get_pricing_tiers($agreement_item_id)
    {
        $this->db->where('agreement_item_id', $agreement_item_id);
        $this->db->order_by('from_month', 'ASC');
        return $this->db->get(db_prefix() . 'equipment_agreement_pricing_tiers')->result_array();
    }

    /**
     * Get single pricing tier
     * @param int $tier_id Tier ID
     * @return object Pricing tier
     */
    public function get_pricing_tier($tier_id)
    {
        $this->db->where('id', $tier_id);
        return $this->db->get(db_prefix() . 'equipment_agreement_pricing_tiers')->row();
    }

    /**
     * Add pricing tier
     * @param array $data Tier data
     * @return int Tier ID
     */
    public function add_pricing_tier($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'equipment_agreement_pricing_tiers', $data);
        return $this->db->insert_id();
    }

    /**
     * Update pricing tier
     * @param array $data Tier data
     * @param int $tier_id Tier ID
     * @return bool Success status
     */
    public function update_pricing_tier($data, $tier_id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $tier_id);
        $this->db->update(db_prefix() . 'equipment_agreement_pricing_tiers', $data);
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Delete pricing tier
     * @param int $tier_id Tier ID
     * @return int Affected rows
     */
    public function delete_pricing_tier($tier_id)
    {
        $this->db->where('id', $tier_id);
        $this->db->delete(db_prefix() . 'equipment_agreement_pricing_tiers');
        return $this->db->affected_rows();
    }

    /**
     * Get applicable pricing tier for a specific month
     * @param int $agreement_item_id Agreement item ID
     * @param int $month_number Month number from agreement start (1, 2, 3, ...)
     * @return object|null Pricing tier or null if not found
     */
    public function get_pricing_tier_for_month($agreement_item_id, $month_number)
    {
        $this->db->where('agreement_item_id', $agreement_item_id);
        $this->db->where('from_month <=', $month_number);
        $this->db->group_start();
        $this->db->where('to_month >=', $month_number);
        $this->db->or_where('to_month IS NULL');
        $this->db->group_end();
        $this->db->order_by('from_month', 'DESC');
        $this->db->limit(1);

        return $this->db->get(db_prefix() . 'equipment_agreement_pricing_tiers')->row();
    }

    // ========== Agreement Charges Methods ==========

    /**
     * Get charges for an agreement
     * @param int $agreement_id Agreement ID
     * @param int $agreement_item_id Optional filter by item ID
     * @return array Charges
     */
    public function get_agreement_charges($agreement_id, $agreement_item_id = null)
    {
        $this->db->where('agreement_id', $agreement_id);
        if ($agreement_item_id !== null) {
            $this->db->where('agreement_item_id', $agreement_item_id);
        }
        $this->db->order_by('charge_type', 'ASC');
        return $this->db->get(db_prefix() . 'equipment_agreement_charges')->result_array();
    }

    /**
     * Get single charge
     * @param int $charge_id Charge ID
     * @return object Charge
     */
    public function get_agreement_charge($charge_id)
    {
        $this->db->where('id', $charge_id);
        return $this->db->get(db_prefix() . 'equipment_agreement_charges')->row();
    }

    /**
     * Add agreement charge
     * @param array $data Charge data
     * @return int Charge ID
     */
    public function add_agreement_charge($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'equipment_agreement_charges', $data);
        return $this->db->insert_id();
    }

    /**
     * Update agreement charge
     * @param array $data Charge data
     * @param int $charge_id Charge ID
     * @return bool Success status
     */
    public function update_agreement_charge($data, $charge_id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $charge_id);
        $this->db->update(db_prefix() . 'equipment_agreement_charges', $data);
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Delete agreement charge
     * @param int $charge_id Charge ID
     * @return int Affected rows
     */
    public function delete_agreement_charge($charge_id)
    {
        $this->db->where('id', $charge_id);
        $this->db->delete(db_prefix() . 'equipment_agreement_charges');
        return $this->db->affected_rows();
    }

    // ========== Utility Methods ==========

    /**
     * Generate next agreement number
     * @param string $type 'supplier' or 'client'
     * @return string Agreement number (e.g., AGR-SUP-2025-001, AGR-CLI-2025-001)
     */
    public function generate_agreement_number($type)
    {
        $prefix = $type === 'supplier' ? 'AGR-SUP-' : 'AGR-CLI-';
        $year = date('Y');

        // Get last agreement number for this type and year
        $this->db->select('agreement_number');
        $this->db->where('agreement_type', $type);
        $this->db->like('agreement_number', $prefix . $year, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row();

        if ($last) {
            // Extract number and increment
            $parts = explode('-', $last->agreement_number);
            $number = isset($parts[3]) ? (int)$parts[3] + 1 : 1;
        } else {
            $number = 1;
        }

        return $prefix . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check if agreement number exists
     * @param string $agreement_number Agreement number
     * @param int $exclude_id Optional ID to exclude from check (for updates)
     * @return bool True if exists
     */
    public function agreement_number_exists($agreement_number, $exclude_id = null)
    {
        $this->db->where('agreement_number', $agreement_number);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    /**
     * Calculate month number from agreement start date
     * @param string $agreement_start_date Agreement start date (Y-m-d)
     * @param string $current_date Current date (Y-m-d)
     * @return int Month number (1, 2, 3, ...)
     */
    public function calculate_month_number($agreement_start_date, $current_date)
    {
        $start = new DateTime($agreement_start_date);
        $current = new DateTime($current_date);

        $interval = $start->diff($current);
        $months = ($interval->y * 12) + $interval->m + 1; // +1 because first month is month 1

        return max(1, $months);
    }
}
