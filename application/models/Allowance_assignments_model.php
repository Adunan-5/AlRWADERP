<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Allowance_assignments_model extends App_Model
{
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'allowance_assignments';
    }

    /**
     * Get assignment(s)
     * @param int $id Optional ID to get specific assignment
     * @return mixed
     */
    public function get($id = null)
    {
        if ($id) {
            return $this->db->where('id', $id)->get($this->table)->row();
        }

        return $this->db->get($this->table)->result_array();
    }

    /**
     * Add new assignment
     * @param array $data
     * @return int|bool Insert ID on success, false on failure
     */
    public function add($data)
    {
        // Check if assignment already exists
        $exists = $this->db->where('allowance_type_id', $data['allowance_type_id'])
                           ->where('employee_type', $data['employee_type'])
                           ->where('employee_type_id', $data['employee_type_id'])
                           ->get($this->table)
                           ->row();

        if ($exists) {
            return false; // Duplicate assignment
        }

        $insert = [
            'allowance_type_id' => (int)$data['allowance_type_id'],
            'employee_type'     => $data['employee_type'],
            'employee_type_id'  => (int)$data['employee_type_id'],
            'is_mandatory'      => isset($data['is_mandatory']) ? (int)$data['is_mandatory'] : 0,
            'default_amount'    => isset($data['default_amount']) && !empty($data['default_amount']) ? $data['default_amount'] : null,
            'created_at'        => date('Y-m-d H:i:s'),
            'created_by'        => get_staff_user_id(),
        ];

        $this->db->insert($this->table, $insert);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('Allowance Assignment Added [ID: ' . $insert_id . ', Allowance: ' . $data['allowance_type_id'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update assignment
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $update = [
            'is_mandatory'   => isset($data['is_mandatory']) ? (int)$data['is_mandatory'] : 0,
            'default_amount' => isset($data['default_amount']) && !empty($data['default_amount']) ? $data['default_amount'] : null,
        ];

        $this->db->where('id', $id);
        return $this->db->update($this->table, $update);
    }

    /**
     * Delete assignment
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $assignment = $this->get($id);

        if (!$assignment) {
            return false;
        }

        $this->db->where('id', $id);
        $deleted = $this->db->delete($this->table);

        if ($deleted) {
            log_activity('Allowance Assignment Deleted [ID: ' . $id . ']');
        }

        return $deleted;
    }

    /**
     * Get all assignments for a specific allowance type
     * @param int $allowance_id
     * @return array
     */
    public function get_by_allowance($allowance_id)
    {
        $this->db->select('aa.*, at.name as allowance_name');
        $this->db->from($this->table . ' aa');
        $this->db->join(db_prefix() . 'allowance_types at', 'at.id = aa.allowance_type_id');
        $this->db->where('aa.allowance_type_id', $allowance_id);

        return $this->db->get()->result_array();
    }

    /**
     * Get allowances assigned to a specific employee type
     * @param string $type 'company_type' or 'profession_type'
     * @param int $type_id
     * @return array
     */
    public function get_by_employee_type($type, $type_id)
    {
        $this->db->select('at.*, aa.is_mandatory, aa.default_amount, aa.id as assignment_id');
        $this->db->from(db_prefix() . 'allowance_types at');
        $this->db->join($this->table . ' aa', 'aa.allowance_type_id = at.id');
        $this->db->where('aa.employee_type', $type);
        $this->db->where('aa.employee_type_id', $type_id);
        $this->db->where('at.is_active', 1);
        $this->db->order_by('at.sort_order', 'asc');
        $this->db->order_by('at.name', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Delete all assignments for a specific allowance type
     * @param int $allowance_id
     * @return bool
     */
    public function delete_by_allowance($allowance_id)
    {
        $this->db->where('allowance_type_id', $allowance_id);
        return $this->db->delete($this->table);
    }

    /**
     * Delete all assignments for a specific employee type
     * @param string $type 'company_type' or 'profession_type'
     * @param int $type_id
     * @return bool
     */
    public function delete_by_employee_type($type, $type_id)
    {
        $this->db->where('employee_type', $type);
        $this->db->where('employee_type_id', $type_id);
        return $this->db->delete($this->table);
    }

    /**
     * Check if assignment exists
     * @param int $allowance_id
     * @param string $type
     * @param int $type_id
     * @return bool
     */
    public function assignment_exists($allowance_id, $type, $type_id)
    {
        $result = $this->db->where('allowance_type_id', $allowance_id)
                           ->where('employee_type', $type)
                           ->where('employee_type_id', $type_id)
                           ->get($this->table)
                           ->row();

        return $result ? true : false;
    }

    /**
     * Get assignments with employee type names
     * @param int $allowance_id
     * @return array
     */
    public function get_by_allowance_with_names($allowance_id)
    {
        $assignments = $this->get_by_allowance($allowance_id);

        foreach ($assignments as &$assignment) {
            if ($assignment['employee_type'] == 'staff_type') {
                $type = $this->db->where('id', $assignment['employee_type_id'])
                                 ->get(db_prefix() . 'stafftype')
                                 ->row();
                $assignment['type_name'] = $type ? $type->name : 'Unknown';
                $assignment['type_name_arabic'] = '';
            } elseif ($assignment['employee_type'] == 'company_type') {
                $type = $this->db->where('id', $assignment['employee_type_id'])
                                 ->get(db_prefix() . 'companytype')
                                 ->row();
                $assignment['type_name'] = $type ? $type->name : 'Unknown';
                $assignment['type_name_arabic'] = $type && isset($type->name_arabic) ? $type->name_arabic : '';
            } else {
                $type = $this->db->where('id', $assignment['employee_type_id'])
                                 ->get(db_prefix() . 'professiontype')
                                 ->row();
                $assignment['type_name'] = $type ? $type->name : 'Unknown';
                $assignment['type_name_arabic'] = $type && isset($type->name_arabic) ? $type->name_arabic : '';
            }
        }

        return $assignments;
    }

    /**
     * Bulk add assignments
     * @param int $allowance_id
     * @param array $assignments Array of ['type' => 'company_type', 'type_id' => 1, ...]
     * @return int Number of assignments added
     */
    public function bulk_add($allowance_id, $assignments)
    {
        $added = 0;

        foreach ($assignments as $assignment) {
            $data = [
                'allowance_type_id' => $allowance_id,
                'employee_type'     => $assignment['type'],
                'employee_type_id'  => $assignment['type_id'],
                'is_mandatory'      => isset($assignment['is_mandatory']) ? $assignment['is_mandatory'] : 0,
                'default_amount'    => isset($assignment['default_amount']) ? $assignment['default_amount'] : null,
            ];

            if ($this->add($data)) {
                $added++;
            }
        }

        return $added;
    }
}
