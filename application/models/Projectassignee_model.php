<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Projectassignee_model extends App_Model
{

    protected $table = 'tblprojectassignee';

    public function __construct()
    {
        parent::__construct();
    }

    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows() > 0;
    }

    public function get_by_staff($staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        return $this->db->get($this->table)->result_array();
    }

    public function get($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function get_assignment_by_id($id)
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'projectassignee')->row();
    }

    /**
     * Check if project assignment has any dependent data
     * Returns array with 'can_delete' boolean and 'reasons' array
     */
    public function check_dependencies($id)
    {
        $assignment = $this->get($id);
        if (!$assignment) {
            return ['can_delete' => false, 'reasons' => ['Assignment not found']];
        }

        $reasons = [];

        // Check for timesheet entries
        $this->db->where('project_id', $assignment->project_id);
        $this->db->where('staff_id', $assignment->staff_id);
        $timesheet_count = $this->db->count_all_results(db_prefix() . 'timesheet_master');

        if ($timesheet_count > 0) {
            $reasons[] = "This assignment has {$timesheet_count} timesheet record(s)";
        }

        // Check for payroll records during the assignment period
        if ($assignment->start_date && $assignment->end_date) {
            $start_month = date('Y-m-01', strtotime($assignment->start_date));
            $end_month = date('Y-m-01', strtotime($assignment->end_date));

            $this->db->where('staff_id', $assignment->staff_id);
            $this->db->where('month >=', $start_month);
            $this->db->where('month <=', $end_month);
            $payroll_count = $this->db->count_all_results(db_prefix() . 'hrp_employees_value');

            if ($payroll_count > 0) {
                $reasons[] = "Payroll records exist for this period ({$payroll_count} month(s))";
            }
        }

        // Check if assignment is historical (ended more than 30 days ago)
        if ($assignment->end_date) {
            $end_timestamp = strtotime($assignment->end_date);
            $thirty_days_ago = strtotime('-30 days');

            if ($end_timestamp < $thirty_days_ago) {
                $reasons[] = "This is a historical assignment (ended more than 30 days ago)";
            }
        }

        return [
            'can_delete' => empty($reasons),
            'reasons' => $reasons,
            'assignment' => $assignment
        ];
    }

    /**
     * Soft delete - mark as inactive instead of removing
     */
    public function soft_delete($id)
    {
        // Check if table has 'active' or 'deleted' column
        // If not, we'll need to add it via migration
        $data = [
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => get_staff_user_id()
        ];

        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Hard delete - only for assignments with no dependencies
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows() > 0;
    }
}
