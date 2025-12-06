<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff_vacation_model extends App_Model
{
    protected $table = 'tblstaffvacations';

    public function add($data)
    {
        $insert = [
            'staff_id'          => $data['staff_id'],
            'vacation_type'     => $data['vacation_type'],
            'start_date'        => to_sql_date($data['start_date']),
            'expected_end_date' => to_sql_date($data['expected_end_date']),
            'end_date'          => !empty($data['end_date']) ? to_sql_date($data['end_date']) : null,
            'comments'          => $data['comments'],
            'status'            => $data['status'],
        ];

        $this->db->insert($this->table, $insert);
        return $this->db->insert_id();
    }

    public function get($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function update($id, $data)
    {
        $update = [
            'vacation_type'     => $data['vacation_type'],
            'start_date'        => to_sql_date($data['start_date']),
            'expected_end_date' => to_sql_date($data['expected_end_date']),
            'end_date'          => !empty($data['end_date']) ? to_sql_date($data['end_date']) : null,
            'comments'          => $data['comments'],
            'status'            => $data['status'],
        ];

        $this->db->where('id', $id);
        return $this->db->update($this->table, $update);
    }

    /**
     * Check if vacation has any dependent data or constraints
     * Returns array with 'can_delete' boolean and 'reasons' array
     */
    public function check_dependencies($id)
    {
        $vacation = $this->get($id);
        if (!$vacation) {
            return ['can_delete' => false, 'reasons' => ['Vacation not found']];
        }

        $reasons = [];

        // Check if vacation is in the past and completed
        if ($vacation->end_date) {
            $end_timestamp = strtotime($vacation->end_date);
            $thirty_days_ago = strtotime('-30 days');

            // If vacation ended more than 30 days ago and status is completed
            if ($end_timestamp < $thirty_days_ago && $vacation->status === 'completed') {
                $reasons[] = "This is a completed historical vacation (ended more than 30 days ago)";
            }
        }

        // Check for payroll records during vacation period
        if ($vacation->start_date && $vacation->end_date) {
            $start_month = date('Y-m-01', strtotime($vacation->start_date));
            $end_month = date('Y-m-01', strtotime($vacation->end_date));

            $this->db->where('staff_id', $vacation->staff_id);
            $this->db->where('month >=', $start_month);
            $this->db->where('month <=', $end_month);
            $payroll_count = $this->db->count_all_results(db_prefix() . 'hrp_employees_value');

            if ($payroll_count > 0) {
                $reasons[] = "Payroll records exist for this vacation period ({$payroll_count} month(s))";
            }
        }

        return [
            'can_delete' => empty($reasons),
            'reasons' => $reasons,
            'vacation' => $vacation
        ];
    }

    public function delete($id)
    {
        // Get vacation details for logging before deletion
        $vacation = $this->get($id);

        if (!$vacation) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->delete($this->table);

        if ($this->db->affected_rows() > 0) {
            log_activity('Vacation Deleted [ID: ' . $id . ', Staff ID: ' . $vacation->staff_id . ', Type: ' . $vacation->vacation_type . ']');
            return true;
        }

        return false;
    }

}
