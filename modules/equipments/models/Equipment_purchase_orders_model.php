<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipment_purchase_orders_model extends App_Model
{
    private $table_purchase_orders;
    private $table_po_items;
    private $table_po_pricing_tiers;
    private $table_po_charges;

    public function __construct()
    {
        parent::__construct();

        $this->table_purchase_orders = db_prefix() . 'equipment_purchase_orders';
        $this->table_po_items = db_prefix() . 'equipment_po_items';
        $this->table_po_pricing_tiers = db_prefix() . 'equipment_po_pricing_tiers';
        $this->table_po_charges = db_prefix() . 'equipment_po_charges';
    }

    // ==================== Purchase Orders CRUD ====================

    /**
     * Get purchase order(s)
     * @param int $id Optional PO ID
     * @return mixed Single object, array of objects, or null
     */
    public function get($id = null)
    {
        if ($id) {
            return $this->db->where('id', $id)->get($this->table_purchase_orders)->row();
        }

        return $this->db->order_by('id', 'DESC')->get($this->table_purchase_orders)->result();
    }

    /**
     * Add new purchase order
     * @param array $data PO data
     * @return int|bool PO ID on success, false on failure
     */
    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table_purchase_orders, $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Equipment Purchase Order Created [ID: ' . $insert_id . ', Number: ' . $data['po_number'] . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update purchase order
     * @param array $data Update data
     * @param int $id PO ID
     * @return bool Success status
     */
    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table_purchase_orders, $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Equipment Purchase Order Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete purchase order
     * @param int $id PO ID
     * @return bool Success status
     */
    public function delete($id)
    {
        $po = $this->get($id);
        if (!$po) {
            return false;
        }

        // Items, tiers, and charges will be cascade deleted by FK constraints
        $this->db->where('id', $id);
        $this->db->delete($this->table_purchase_orders);

        if ($this->db->affected_rows() > 0) {
            log_activity('Equipment Purchase Order Deleted [ID: ' . $id . ', Number: ' . $po->po_number . ']');
            return true;
        }

        return false;
    }

    /**
     * Generate unique PO number
     * @return string PO number (e.g., PO-2025-001)
     */
    public function generate_po_number()
    {
        $year = date('Y');
        $prefix = 'PO-' . $year . '-';

        // Get the last PO number for this year
        $this->db->select('po_number');
        $this->db->like('po_number', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last_po = $this->db->get($this->table_purchase_orders)->row();

        if ($last_po) {
            // Extract the sequence number and increment
            $last_sequence = (int)substr($last_po->po_number, -3);
            $new_sequence = $last_sequence + 1;
        } else {
            $new_sequence = 1;
        }

        return $prefix . str_pad($new_sequence, 3, '0', STR_PAD_LEFT);
    }

    // ==================== PO Items CRUD ====================

    /**
     * Get PO items
     * @param int $po_id Purchase Order ID
     * @return array Array of items with equipment details
     */
    public function get_po_items($po_id)
    {
        $this->db->select('
            pi.*,
            e.name as equipment_name,
            e.platenumber_code,
            e.equipmenttype,
            o.name as operator_name,
            o.nationality as operator_nationality
        ');
        $this->db->from($this->table_po_items . ' pi');
        $this->db->join(db_prefix() . 'equipments e', 'e.id = pi.equipment_id', 'left');
        $this->db->join(db_prefix() . 'operators o', 'o.id = pi.operator_id', 'left');
        $this->db->where('pi.po_id', $po_id);
        $this->db->order_by('pi.id', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Add PO item
     * @param array $data Item data
     * @return int|bool Item ID on success, false on failure
     */
    public function add_po_item($data)
    {
        $this->db->insert($this->table_po_items, $data);
        return $this->db->insert_id();
    }

    /**
     * Update PO item
     * @param array $data Update data
     * @param int $item_id Item ID
     * @return bool Success status
     */
    public function update_po_item($data, $item_id)
    {
        $this->db->where('id', $item_id);
        $this->db->update($this->table_po_items, $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete PO item (cascade deletes pricing tiers)
     * @param int $item_id Item ID
     * @return bool Success status
     */
    public function delete_po_item($item_id)
    {
        $this->db->where('id', $item_id);
        $this->db->delete($this->table_po_items);
        return $this->db->affected_rows() > 0;
    }

    // ==================== Pricing Tiers CRUD ====================

    /**
     * Get pricing tiers for a PO item
     * @param int $po_item_id PO Item ID
     * @return array Array of pricing tiers
     */
    public function get_pricing_tiers($po_item_id)
    {
        $this->db->where('po_item_id', $po_item_id);
        $this->db->order_by('from_month', 'ASC');
        return $this->db->get($this->table_po_pricing_tiers)->result_array();
    }

    /**
     * Get pricing tier for a specific month
     * @param int $po_item_id PO Item ID
     * @param int $month_number Month number (e.g., 1, 12, 13)
     * @return object|null Pricing tier object or null
     */
    public function get_pricing_tier_for_month($po_item_id, $month_number)
    {
        $this->db->where('po_item_id', $po_item_id);
        $this->db->where('from_month <=', $month_number);
        $this->db->group_start();
        $this->db->where('to_month >=', $month_number);
        $this->db->or_where('to_month IS NULL');
        $this->db->group_end();
        $this->db->order_by('from_month', 'DESC');
        $this->db->limit(1);

        return $this->db->get($this->table_po_pricing_tiers)->row();
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

        $this->db->insert($this->table_po_pricing_tiers, $data);
        return $this->db->insert_id();
    }

    /**
     * Update pricing tier
     * @param array $data Update data
     * @param int $tier_id Tier ID
     * @return bool Success status
     */
    public function update_pricing_tier($data, $tier_id)
    {
        // Convert empty to_month to NULL
        if (isset($data['to_month']) && $data['to_month'] === '') {
            $data['to_month'] = null;
        }

        $this->db->where('id', $tier_id);
        $this->db->update($this->table_po_pricing_tiers, $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete pricing tier
     * @param int $tier_id Tier ID
     * @return bool Success status
     */
    public function delete_pricing_tier($tier_id)
    {
        $this->db->where('id', $tier_id);
        $this->db->delete($this->table_po_pricing_tiers);
        return $this->db->affected_rows() > 0;
    }

    // ==================== Charges CRUD ====================

    /**
     * Get PO charges
     * @param int $po_id Purchase Order ID
     * @return array Array of charges
     */
    public function get_po_charges($po_id)
    {
        $this->db->where('po_id', $po_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get($this->table_po_charges)->result_array();
    }

    /**
     * Add PO charge
     * @param array $data Charge data
     * @return int|bool Charge ID on success, false on failure
     */
    public function add_po_charge($data)
    {
        $this->db->insert($this->table_po_charges, $data);
        return $this->db->insert_id();
    }

    /**
     * Update PO charge
     * @param array $data Update data
     * @param int $charge_id Charge ID
     * @return bool Success status
     */
    public function update_po_charge($data, $charge_id)
    {
        $this->db->where('id', $charge_id);
        $this->db->update($this->table_po_charges, $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete PO charge
     * @param int $charge_id Charge ID
     * @return bool Success status
     */
    public function delete_po_charge($charge_id)
    {
        $this->db->where('id', $charge_id);
        $this->db->delete($this->table_po_charges);
        return $this->db->affected_rows() > 0;
    }

    // ==================== Utility Methods ====================

    /**
     * Calculate total PO value (items + charges)
     * @param int $po_id Purchase Order ID
     * @param int $months Number of months for calculation
     * @return float Total amount
     */
    public function calculate_po_total($po_id, $months = 12)
    {
        $total = 0;

        // Calculate items total (using first tier pricing as base)
        $items = $this->get_po_items($po_id);
        foreach ($items as $item) {
            $tiers = $this->get_pricing_tiers($item['id']);
            if (!empty($tiers)) {
                // Use first tier monthly rate
                $total += ($tiers[0]['monthly_rate'] * $item['quantity'] * $months);
            }
        }

        // Add charges
        $charges = $this->get_po_charges($po_id);
        foreach ($charges as $charge) {
            $total += $charge['amount'];
        }

        return $total;
    }

    /**
     * Change PO status
     * @param int $po_id Purchase Order ID
     * @param string $status New status
     * @return bool Success status
     */
    public function change_status($po_id, $status)
    {
        $valid_statuses = ['draft', 'sent', 'confirmed', 'partially_received', 'completed', 'cancelled'];

        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $this->db->where('id', $po_id);
        $this->db->update($this->table_purchase_orders, ['status' => $status]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Equipment PO Status Changed [ID: ' . $po_id . ', Status: ' . $status . ']');
            return true;
        }

        return false;
    }

    /**
     * Copy quotation items to PO items
     * @param int $quotation_id Source quotation ID
     * @param int $po_id Target PO ID
     * @return bool Success status
     */
    public function copy_quotation_items_to_po($quotation_id, $po_id)
    {
        // Get quotation items with RFQ item data (if linked)
        $this->db->select('
            qi.*,
            ri.equipment_id,
            ri.operator_id
        ');
        $this->db->from(db_prefix() . 'equipment_quotation_items qi');
        $this->db->join(db_prefix() . 'equipment_rfq_items ri', 'ri.id = qi.rfq_item_id', 'left');
        $this->db->where('qi.quotation_id', $quotation_id);
        $quotation_items = $this->db->get()->result();

        if (empty($quotation_items)) {
            return false;
        }

        // Copy each item to PO items
        foreach ($quotation_items as $item) {
            $po_item_data = [
                'po_id' => $po_id,
                'item_type' => $item->item_type,
                'equipment_id' => $item->equipment_id,
                'equipment_description' => $item->equipment_description,
                'operator_id' => $item->operator_id,
                'operator_description' => $item->operator_description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'standard_hours_per_day' => $item->standard_hours_per_day,
                'days_per_month' => $item->days_per_month,
                'overtime_rate_multiplier' => 1.50, // Default overtime multiplier
                'notes' => $item->notes,
            ];

            $this->db->insert(db_prefix() . 'equipment_po_items', $po_item_data);
        }

        log_activity('Copied quotation items to PO [Quotation ID: ' . $quotation_id . ', PO ID: ' . $po_id . ']');
        return true;
    }
}
