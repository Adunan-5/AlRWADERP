<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Allowance_types_model extends App_Model
{
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'allowance_types';
    }

    /**
     * Get allowance type(s)
     * @param int $id Optional ID to get specific allowance type
     * @return mixed
     */
    public function get($id = null)
    {
        if ($id) {
            return $this->db->where('id', $id)->get($this->table)->row();
        }

        return $this->db->order_by('sort_order', 'asc')
                        ->order_by('name', 'asc')
                        ->get($this->table)
                        ->result_array();
    }

    /**
     * Get only active allowance types
     * @return array
     */
    public function get_active()
    {
        return $this->db->where('is_active', 1)
                        ->order_by('sort_order', 'asc')
                        ->order_by('name', 'asc')
                        ->get($this->table)
                        ->result_array();
    }

    /**
     * Add new allowance type
     * @param array $data
     * @return int|bool Insert ID on success, false on failure
     */
    public function add($data)
    {
        // Check for duplicate name
        if ($this->check_duplicate($data['name'])) {
            return false;
        }

        $insert = [
            'name'         => trim($data['name']),
            'name_arabic'  => isset($data['name_arabic']) ? trim($data['name_arabic']) : null,
            'description'  => isset($data['description']) ? trim($data['description']) : null,
            'is_active'    => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'sort_order'   => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'created_by'   => get_staff_user_id(),
        ];

        $this->db->insert($this->table, $insert);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Allowance Type Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update allowance type
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        // Check for duplicate name (excluding current record)
        if ($this->check_duplicate($data['name'], $id)) {
            return false;
        }

        $update = [
            'name'         => trim($data['name']),
            'name_arabic'  => isset($data['name_arabic']) ? trim($data['name_arabic']) : null,
            'description'  => isset($data['description']) ? trim($data['description']) : null,
            'is_active'    => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'sort_order'   => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
            'modified_at'  => date('Y-m-d H:i:s'),
            'modified_by'  => get_staff_user_id(),
        ];

        $this->db->where('id', $id);
        $success = $this->db->update($this->table, $update);

        if ($success) {
            log_activity('Allowance Type Updated [ID: ' . $id . ', Name: ' . $data['name'] . ']');
        }

        return $success;
    }

    /**
     * Delete allowance type (with validation)
     * @param int $id
     * @return array Result with success status and message
     */
    public function delete($id)
    {
        $allowance = $this->get($id);

        if (!$allowance) {
            return [
                'success' => false,
                'message' => _l('allowance_type_not_found')
            ];
        }

        // Check if allowance is assigned to any employee types
        $assignment_count = $this->db->where('allowance_type_id', $id)
                                     ->count_all_results(db_prefix() . 'allowance_assignments');

        if ($assignment_count > 0) {
            return [
                'success' => false,
                'message' => _l('allowance_type_has_assignments'),
                'assignments' => $assignment_count
            ];
        }

        // Check if allowance is used in any pay records
        $usage_count = $this->db->where('allowance_type_id', $id)
                                ->count_all_results(db_prefix() . 'staff_pay_allowances');

        if ($usage_count > 0) {
            return [
                'success' => false,
                'message' => _l('allowance_type_in_use'),
                'usage_count' => $usage_count
            ];
        }

        // Safe to delete
        $this->db->where('id', $id);
        $deleted = $this->db->delete($this->table);

        if ($deleted) {
            log_activity('Allowance Type Deleted [ID: ' . $id . ', Name: ' . $allowance->name . ']');
        }

        return [
            'success' => $deleted,
            'message' => $deleted ? _l('deleted', _l('allowance_type')) : _l('problem_deleting', _l('allowance_type'))
        ];
    }

    /**
     * Check if allowance name already exists
     * @param string $name
     * @param int $exclude_id Optional ID to exclude from check
     * @return bool
     */
    public function check_duplicate($name, $exclude_id = null)
    {
        $this->db->where('LOWER(name)', strtolower(trim($name)));

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        $result = $this->db->get($this->table)->row();

        return $result ? true : false;
    }

    /**
     * Get usage count for an allowance type
     * @param int $id
     * @return int
     */
    public function get_usage_count($id)
    {
        return $this->db->where('allowance_type_id', $id)
                        ->count_all_results(db_prefix() . 'staff_pay_allowances');
    }

    /**
     * Get assignment count for an allowance type
     * @param int $id
     * @return int
     */
    public function get_assignment_count($id)
    {
        return $this->db->where('allowance_type_id', $id)
                        ->count_all_results(db_prefix() . 'allowance_assignments');
    }

    /**
     * Update sort order for an allowance type
     * @param int $id
     * @param int $order
     * @return bool
     */
    public function update_sort_order($id, $order)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['sort_order' => (int)$order]);
    }

    /**
     * Toggle active status
     * @param int $id
     * @return bool
     */
    public function toggle_active($id)
    {
        $allowance = $this->get($id);

        if (!$allowance) {
            return false;
        }

        $new_status = $allowance->is_active == 1 ? 0 : 1;

        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'is_active' => $new_status,
            'modified_at' => date('Y-m-d H:i:s'),
            'modified_by' => get_staff_user_id()
        ]);
    }

    /**
     * Get allowance types with usage statistics
     * @return array
     */
    public function get_with_stats()
    {
        $allowances = $this->get();

        foreach ($allowances as &$allowance) {
            $allowance['usage_count'] = $this->get_usage_count($allowance['id']);
            $allowance['assignment_count'] = $this->get_assignment_count($allowance['id']);
        }

        return $allowances;
    }
}
