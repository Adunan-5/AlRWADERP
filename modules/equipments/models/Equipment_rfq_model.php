<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Equipment_rfq_model extends App_Model
{
    private $table_rfq;
    private $table_rfq_items;
    private $table_rfq_suppliers;

    public function __construct()
    {
        parent::__construct();
        $this->table_rfq = db_prefix() . 'equipment_rfq';
        $this->table_rfq_items = db_prefix() . 'equipment_rfq_items';
        $this->table_rfq_suppliers = db_prefix() . 'equipment_rfq_suppliers';
    }

    // ========== RFQ Master CRUD ==========

    /**
     * Get all RFQs with supplier info
     * @return array
     */
    public function get_all()
    {
        $this->db->select('
            rfq.*,
            CONCAT(staff.firstname, " ", staff.lastname) as created_by_name,
            (SELECT COUNT(*) FROM ' . $this->table_rfq_items . ' WHERE rfq_id = rfq.id) as items_count,
            (SELECT COUNT(*) FROM ' . $this->table_rfq_suppliers . ' WHERE rfq_id = rfq.id) as suppliers_count
        ');
        $this->db->from($this->table_rfq . ' rfq');
        $this->db->join(db_prefix() . 'staff staff', 'staff.staffid = rfq.created_by', 'left');
        $this->db->order_by('rfq.id', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get single RFQ by ID
     * @param int $id
     * @return object|null
     */
    public function get($id)
    {
        $this->db->select('
            rfq.*,
            CONCAT(staff.firstname, " ", staff.lastname) as created_by_name,
            c.company as client_name
        ');
        $this->db->from($this->table_rfq . ' rfq');
        $this->db->join(db_prefix() . 'staff staff', 'staff.staffid = rfq.created_by', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = rfq.client_id', 'left');
        $this->db->where('rfq.id', $id);

        return $this->db->get()->row();
    }

    /**
     * Add new RFQ
     * @param array $data
     * @return int|bool RFQ ID on success, false on failure
     */
    public function add($data)
    {
        // Generate RFQ number if not provided
        if (!isset($data['rfq_number']) || empty($data['rfq_number'])) {
            $data['rfq_number'] = $this->generate_rfq_number();
        }

        // Set created_by to current staff
        if (!isset($data['created_by'])) {
            $data['created_by'] = get_staff_user_id();
        }

        // Handle date conversions
        if (isset($data['rfq_date'])) {
            $data['rfq_date'] = to_sql_date($data['rfq_date']);
        }
        if (isset($data['required_by_date']) && !empty($data['required_by_date'])) {
            $data['required_by_date'] = to_sql_date($data['required_by_date']);
        }
        if (isset($data['expected_start_date']) && !empty($data['expected_start_date'])) {
            $data['expected_start_date'] = to_sql_date($data['expected_start_date']);
        }

        // Convert empty strings to NULL for nullable fields
        $nullable_fields = ['client_id', 'required_by_date', 'expected_start_date', 'expected_duration_months', 'project_reference', 'terms_conditions', 'notes'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->db->insert($this->table_rfq, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New RFQ Created [ID: ' . $insert_id . ', Number: ' . $data['rfq_number'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update RFQ
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update($data, $id)
    {
        // Handle date conversions
        if (isset($data['rfq_date'])) {
            $data['rfq_date'] = to_sql_date($data['rfq_date']);
        }
        if (isset($data['required_by_date']) && !empty($data['required_by_date'])) {
            $data['required_by_date'] = to_sql_date($data['required_by_date']);
        } else {
            $data['required_by_date'] = null;
        }
        if (isset($data['expected_start_date']) && !empty($data['expected_start_date'])) {
            $data['expected_start_date'] = to_sql_date($data['expected_start_date']);
        } else {
            $data['expected_start_date'] = null;
        }

        // Convert empty strings to NULL for nullable fields
        $nullable_fields = ['client_id', 'expected_duration_months', 'project_reference', 'terms_conditions', 'notes'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->db->where('id', $id);
        $success = $this->db->update($this->table_rfq, $data);

        if ($success) {
            log_activity('RFQ Updated [ID: ' . $id . ']');
        }

        return $success;
    }

    /**
     * Delete RFQ (cascade deletes items and suppliers)
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $rfq = $this->get($id);
        if (!$rfq) {
            return false;
        }

        $this->db->where('id', $id);
        $success = $this->db->delete($this->table_rfq);

        if ($success) {
            log_activity('RFQ Deleted [ID: ' . $id . ', Number: ' . $rfq->rfq_number . ']');
        }

        return $success;
    }

    /**
     * Generate unique RFQ number
     * @return string
     */
    public function generate_rfq_number()
    {
        $year = date('Y');
        $prefix = 'RFQ-' . $year . '-';

        // Get the latest RFQ number for this year
        $this->db->select('rfq_number');
        $this->db->from($this->table_rfq);
        $this->db->like('rfq_number', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $result = $this->db->get()->row();

        if ($result) {
            // Extract the number part and increment
            $last_number = (int) str_replace($prefix, '', $result->rfq_number);
            $new_number = $last_number + 1;
        } else {
            // First RFQ of the year
            $new_number = 1;
        }

        return $prefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);
    }

    // ========== RFQ Items CRUD ==========

    /**
     * Get RFQ items
     * @param int $rfq_id
     * @return array
     */
    public function get_rfq_items($rfq_id)
    {
        $this->db->select('
            ri.*,
            e.name as equipment_name,
            e.platenumber_code,
            e.equipmenttype,
            o.name as operator_name,
            o.nationality as operator_nationality
        ');
        $this->db->from($this->table_rfq_items . ' ri');
        $this->db->join(db_prefix() . 'equipments e', 'e.id = ri.equipment_id', 'left');
        $this->db->join(db_prefix() . 'operators o', 'o.id = ri.operator_id', 'left');
        $this->db->where('ri.rfq_id', $rfq_id);
        $this->db->order_by('ri.id', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Add RFQ item
     * @param array $data
     * @return int|bool Item ID on success, false on failure
     */
    public function add_rfq_item($data)
    {
        // Convert empty strings to NULL for foreign key fields
        if (isset($data['equipment_id']) && $data['equipment_id'] === '') {
            $data['equipment_id'] = null;
        }
        if (isset($data['operator_id']) && $data['operator_id'] === '') {
            $data['operator_id'] = null;
        }

        // Handle nullable fields
        $nullable_fields = ['standard_hours_per_day', 'days_per_month', 'expected_duration_months', 'notes', 'equipment_description', 'operator_description'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->db->insert($this->table_rfq_items, $data);
        return $this->db->insert_id();
    }

    /**
     * Update RFQ item
     * @param array $data
     * @param int $item_id
     * @return bool
     */
    public function update_rfq_item($data, $item_id)
    {
        // Convert empty strings to NULL for foreign key fields
        if (isset($data['equipment_id']) && $data['equipment_id'] === '') {
            $data['equipment_id'] = null;
        }
        if (isset($data['operator_id']) && $data['operator_id'] === '') {
            $data['operator_id'] = null;
        }

        // Handle nullable fields
        $nullable_fields = ['standard_hours_per_day', 'days_per_month', 'expected_duration_months', 'notes', 'equipment_description', 'operator_description'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->db->where('id', $item_id);
        return $this->db->update($this->table_rfq_items, $data);
    }

    /**
     * Delete RFQ item
     * @param int $item_id
     * @return bool
     */
    public function delete_rfq_item($item_id)
    {
        $this->db->where('id', $item_id);
        return $this->db->delete($this->table_rfq_items);
    }

    // ========== RFQ Suppliers CRUD ==========

    /**
     * Get RFQ suppliers
     * @param int $rfq_id
     * @return array
     */
    public function get_rfq_suppliers($rfq_id)
    {
        $this->db->select('
            rs.*,
            s.name as supplier_name,
            s.email as supplier_email
        ');
        $this->db->from($this->table_rfq_suppliers . ' rs');
        $this->db->join(db_prefix() . 'suppliers s', 's.id = rs.supplier_id', 'left');
        $this->db->where('rs.rfq_id', $rfq_id);
        $this->db->order_by('rs.id', 'ASC');

        $suppliers = $this->db->get()->result_array();

        // Format dates for display
        foreach ($suppliers as &$supplier) {
            if (!empty($supplier['sent_date'])) {
                $supplier['sent_date'] = _d($supplier['sent_date']);
            }
            if (!empty($supplier['response_received_date'])) {
                $supplier['response_received_date'] = _d($supplier['response_received_date']);
            }
        }

        return $suppliers;
    }

    /**
     * Add supplier to RFQ
     * @param array $data
     * @return int|bool
     */
    public function add_rfq_supplier($data)
    {
        // Handle date conversion
        if (isset($data['sent_date']) && !empty($data['sent_date'])) {
            $data['sent_date'] = to_sql_date($data['sent_date']);
        }
        if (isset($data['response_received_date']) && !empty($data['response_received_date'])) {
            $data['response_received_date'] = to_sql_date($data['response_received_date']);
        }

        // Handle nullable fields
        $nullable_fields = ['sent_date', 'response_received_date', 'notes'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->db->insert($this->table_rfq_suppliers, $data);
        return $this->db->insert_id();
    }

    /**
     * Update RFQ supplier
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update_rfq_supplier($data, $id)
    {
        // Handle date conversion
        if (isset($data['sent_date']) && !empty($data['sent_date'])) {
            $data['sent_date'] = to_sql_date($data['sent_date']);
        } else {
            $data['sent_date'] = null;
        }
        if (isset($data['response_received_date']) && !empty($data['response_received_date'])) {
            $data['response_received_date'] = to_sql_date($data['response_received_date']);
        } else {
            $data['response_received_date'] = null;
        }

        // Handle nullable fields
        $nullable_fields = ['notes'];
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->db->where('id', $id);
        return $this->db->update($this->table_rfq_suppliers, $data);
    }

    /**
     * Delete RFQ supplier
     * @param int $id
     * @return bool
     */
    public function delete_rfq_supplier($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_rfq_suppliers);
    }
}
