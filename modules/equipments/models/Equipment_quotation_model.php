<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipment_quotation_model extends App_Model
{
    private $table_quotations;
    private $table_quotation_items;

    public function __construct()
    {
        parent::__construct();
        $this->table_quotations = db_prefix() . 'equipment_quotations';
        $this->table_quotation_items = db_prefix() . 'equipment_quotation_items';
    }

    // ========== Quotation Master CRUD ==========

    /**
     * Get all quotations with related data
     * @param int $rfq_id Optional RFQ filter
     * @return array
     */
    public function get_all($rfq_id = null)
    {
        $this->db->select('
            q.*,
            CONCAT(staff.firstname, " ", staff.lastname) as created_by_name,
            s.name as supplier_name,
            s.email as supplier_email,
            rfq.rfq_number,
            rfq.project_reference,
            (SELECT COUNT(*) FROM ' . $this->table_quotation_items . ' WHERE quotation_id = q.id) as items_count
        ');
        $this->db->from($this->table_quotations . ' q');
        $this->db->join(db_prefix() . 'staff staff', 'staff.staffid = q.created_by', 'left');
        $this->db->join(db_prefix() . 'suppliers s', 's.id = q.supplier_id', 'left');
        $this->db->join(db_prefix() . 'equipment_rfq rfq', 'rfq.id = q.rfq_id', 'left');

        if ($rfq_id) {
            $this->db->where('q.rfq_id', $rfq_id);
        }

        $this->db->order_by('q.id', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get single quotation by ID
     * @param int $id
     * @return object|null
     */
    public function get($id)
    {
        $this->db->select('
            q.*,
            CONCAT(staff.firstname, " ", staff.lastname) as created_by_name,
            s.name as supplier_name,
            s.email as supplier_email,
            rfq.rfq_number,
            rfq.project_reference,
            rfq.client_id,
            c.company as client_name
        ');
        $this->db->from($this->table_quotations . ' q');
        $this->db->join(db_prefix() . 'staff staff', 'staff.staffid = q.created_by', 'left');
        $this->db->join(db_prefix() . 'suppliers s', 's.id = q.supplier_id', 'left');
        $this->db->join(db_prefix() . 'equipment_rfq rfq', 'rfq.id = q.rfq_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = rfq.client_id', 'left');
        $this->db->where('q.id', $id);

        return $this->db->get()->row();
    }

    /**
     * Add new quotation
     * @param array $data
     * @return int|bool Quotation ID on success, false on failure
     */
    public function add($data)
    {
        // Generate quotation number if not provided
        if (!isset($data['quotation_number']) || empty($data['quotation_number'])) {
            $data['quotation_number'] = $this->generate_quotation_number();
        }

        // Set created_by to current staff if not set
        if (!isset($data['created_by'])) {
            $data['created_by'] = get_staff_user_id();
        }

        // Handle date conversions
        if (isset($data['quotation_date'])) {
            $data['quotation_date'] = to_sql_date($data['quotation_date']);
        }
        if (isset($data['valid_until_date']) && !empty($data['valid_until_date'])) {
            $data['valid_until_date'] = to_sql_date($data['valid_until_date']);
        }

        // Convert empty strings to NULL for nullable fields
        $nullable_fields = ['valid_until_date', 'tax_percentage', 'tax_amount', 'payment_terms', 'delivery_terms', 'notes', 'created_by'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Set default amounts if not provided
        if (!isset($data['subtotal'])) {
            $data['subtotal'] = 0.00;
        }
        if (!isset($data['total_amount'])) {
            $data['total_amount'] = 0.00;
        }

        $this->db->insert($this->table_quotations, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Quotation Created [ID: ' . $insert_id . ', Number: ' . $data['quotation_number'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update quotation
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update($data, $id)
    {
        // Handle date conversions
        if (isset($data['quotation_date'])) {
            $data['quotation_date'] = to_sql_date($data['quotation_date']);
        }
        if (isset($data['valid_until_date']) && !empty($data['valid_until_date'])) {
            $data['valid_until_date'] = to_sql_date($data['valid_until_date']);
        } else {
            $data['valid_until_date'] = null;
        }

        // Convert empty strings to NULL for nullable fields
        $nullable_fields = ['tax_percentage', 'tax_amount', 'payment_terms', 'delivery_terms', 'notes'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->db->where('id', $id);
        $success = $this->db->update($this->table_quotations, $data);

        if ($success) {
            log_activity('Quotation Updated [ID: ' . $id . ']');
        }

        return $success;
    }

    /**
     * Delete quotation (cascade deletes items)
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $quotation = $this->get($id);
        if (!$quotation) {
            return false;
        }

        $this->db->where('id', $id);
        $success = $this->db->delete($this->table_quotations);

        if ($success) {
            log_activity('Quotation Deleted [ID: ' . $id . ', Number: ' . $quotation->quotation_number . ']');
        }

        return $success;
    }

    /**
     * Generate unique quotation number
     * @return string
     */
    public function generate_quotation_number()
    {
        $year = date('Y');
        $prefix = 'QUOT-' . $year . '-';

        // Get the latest quotation number for this year
        $this->db->select('quotation_number');
        $this->db->from($this->table_quotations);
        $this->db->like('quotation_number', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $result = $this->db->get()->row();

        if ($result) {
            // Extract the number part and increment
            $last_number = (int) str_replace($prefix, '', $result->quotation_number);
            $new_number = $last_number + 1;
        } else {
            // First quotation of the year
            $new_number = 1;
        }

        return $prefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate quotation totals from items
     * @param int $quotation_id
     * @return bool
     */
    public function recalculate_totals($quotation_id)
    {
        // Get all items for this quotation
        $this->db->select_sum('line_total');
        $this->db->where('quotation_id', $quotation_id);
        $result = $this->db->get($this->table_quotation_items)->row();

        $subtotal = $result->line_total ? $result->line_total : 0.00;

        // Get quotation to check tax percentage
        $quotation = $this->get($quotation_id);
        $tax_percentage = $quotation->tax_percentage ? $quotation->tax_percentage : 0.00;

        $tax_amount = ($subtotal * $tax_percentage) / 100;
        $total_amount = $subtotal + $tax_amount;

        // Update quotation totals
        $this->db->where('id', $quotation_id);
        return $this->db->update($this->table_quotations, [
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total_amount' => $total_amount
        ]);
    }

    // ========== Quotation Items CRUD ==========

    /**
     * Get quotation items
     * @param int $quotation_id
     * @return array
     */
    public function get_quotation_items($quotation_id)
    {
        $this->db->select('
            qi.*,
            ri.equipment_description as rfq_equipment_description,
            ri.operator_description as rfq_operator_description,
            ri.quantity as rfq_quantity,
            ri.unit as rfq_unit
        ');
        $this->db->from($this->table_quotation_items . ' qi');
        $this->db->join(db_prefix() . 'equipment_rfq_items ri', 'ri.id = qi.rfq_item_id', 'left');
        $this->db->where('qi.quotation_id', $quotation_id);
        $this->db->order_by('qi.id', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Add quotation item
     * @param array $data
     * @return int|bool Item ID on success, false on failure
     */
    public function add_quotation_item($data)
    {
        // Handle nullable fields
        $nullable_fields = ['rfq_item_id', 'standard_hours_per_day', 'days_per_month', 'duration_months', 'notes', 'equipment_description', 'operator_description'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Calculate line total if not provided
        if (!isset($data['line_total']) && isset($data['quantity']) && isset($data['unit_rate'])) {
            $data['line_total'] = $data['quantity'] * $data['unit_rate'];
        }

        $this->db->insert($this->table_quotation_items, $data);
        $item_id = $this->db->insert_id();

        if ($item_id && isset($data['quotation_id'])) {
            // Recalculate quotation totals
            $this->recalculate_totals($data['quotation_id']);
        }

        return $item_id;
    }

    /**
     * Update quotation item
     * @param array $data
     * @param int $item_id
     * @return bool
     */
    public function update_quotation_item($data, $item_id)
    {
        // Handle nullable fields
        $nullable_fields = ['rfq_item_id', 'standard_hours_per_day', 'days_per_month', 'duration_months', 'notes', 'equipment_description', 'operator_description'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Recalculate line total if quantity or unit_rate changed
        if (isset($data['quantity']) || isset($data['unit_rate'])) {
            $item = $this->db->where('id', $item_id)->get($this->table_quotation_items)->row();
            $quantity = isset($data['quantity']) ? $data['quantity'] : $item->quantity;
            $unit_rate = isset($data['unit_rate']) ? $data['unit_rate'] : $item->unit_rate;
            $data['line_total'] = $quantity * $unit_rate;
        }

        $this->db->where('id', $item_id);
        $success = $this->db->update($this->table_quotation_items, $data);

        if ($success) {
            // Get quotation_id to recalculate totals
            $item = $this->db->where('id', $item_id)->get($this->table_quotation_items)->row();
            if ($item) {
                $this->recalculate_totals($item->quotation_id);
            }
        }

        return $success;
    }

    /**
     * Delete quotation item
     * @param int $item_id
     * @return bool
     */
    public function delete_quotation_item($item_id)
    {
        // Get quotation_id before deleting
        $item = $this->db->where('id', $item_id)->get($this->table_quotation_items)->row();

        $this->db->where('id', $item_id);
        $success = $this->db->delete($this->table_quotation_items);

        if ($success && $item) {
            // Recalculate quotation totals
            $this->recalculate_totals($item->quotation_id);
        }

        return $success;
    }

    /**
     * Copy RFQ items to new quotation
     * @param int $rfq_id
     * @param int $quotation_id
     * @return bool
     */
    public function copy_rfq_items_to_quotation($rfq_id, $quotation_id)
    {
        // Get all RFQ items
        $this->db->where('rfq_id', $rfq_id);
        $rfq_items = $this->db->get(db_prefix() . 'equipment_rfq_items')->result_array();

        if (empty($rfq_items)) {
            return false;
        }

        // Insert items into quotation
        foreach ($rfq_items as $rfq_item) {
            $quotation_item = [
                'quotation_id' => $quotation_id,
                'rfq_item_id' => $rfq_item['id'],
                'item_type' => $rfq_item['item_type'],
                'equipment_description' => $rfq_item['equipment_description'],
                'operator_description' => $rfq_item['operator_description'],
                'quantity' => $rfq_item['quantity'],
                'unit' => $rfq_item['unit'],
                'standard_hours_per_day' => $rfq_item['standard_hours_per_day'],
                'days_per_month' => $rfq_item['days_per_month'],
                'duration_months' => $rfq_item['expected_duration_months'],
                'unit_rate' => 0.00, // To be filled by user
                'line_total' => 0.00,
                'notes' => $rfq_item['notes']
            ];

            $this->db->insert($this->table_quotation_items, $quotation_item);
        }

        return true;
    }

    /**
     * Get quotations for comparison (by RFQ)
     * @param int $rfq_id
     * @return array Multi-dimensional array with quotations and their items
     */
    public function get_quotations_for_comparison($rfq_id)
    {
        // Get all submitted/under_review quotations for this RFQ
        $this->db->select('
            q.*,
            s.name as supplier_name
        ');
        $this->db->from($this->table_quotations . ' q');
        $this->db->join(db_prefix() . 'suppliers s', 's.id = q.supplier_id', 'left');
        $this->db->where('q.rfq_id', $rfq_id);
        $this->db->where_in('q.status', ['submitted', 'under_review', 'accepted']);
        $this->db->order_by('q.total_amount', 'ASC'); // Lowest price first
        $quotations = $this->db->get()->result_array();

        if (empty($quotations)) {
            return [];
        }

        // Get items for each quotation
        foreach ($quotations as &$quotation) {
            $quotation['items'] = $this->get_quotation_items($quotation['id']);
        }

        return $quotations;
    }

    /**
     * Accept quotation (and reject others for same RFQ)
     * @param int $quotation_id
     * @return bool
     */
    public function accept_quotation($quotation_id)
    {
        $quotation = $this->get($quotation_id);
        if (!$quotation) {
            return false;
        }

        // Start transaction
        $this->db->trans_start();

        // Reject all other quotations for this RFQ
        $this->db->where('rfq_id', $quotation->rfq_id);
        $this->db->where('id !=', $quotation_id);
        $this->db->update($this->table_quotations, ['status' => 'rejected']);

        // Accept this quotation
        $this->db->where('id', $quotation_id);
        $this->db->update($this->table_quotations, ['status' => 'accepted']);

        // Update RFQ status
        $this->db->where('id', $quotation->rfq_id);
        $this->db->update(db_prefix() . 'equipment_rfq', ['status' => 'evaluated']);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }

        log_activity('Quotation Accepted [ID: ' . $quotation_id . ', Number: ' . $quotation->quotation_number . ']');
        return true;
    }
}
