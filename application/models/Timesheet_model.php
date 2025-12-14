<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Timesheet_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insert_master($data)
    {
        $this->db->insert(db_prefix() . 'timesheet_master', $data);
        return (int)$this->db->insert_id();
    }

    // public function insert_detail($timesheetId, $workDate, $hoursWorked)
    // {
    //     $this->db->insert(db_prefix() . 'timesheet_details', [
    //         'timesheet_id' => (int)$timesheetId,
    //         'work_date'    => $workDate,
    //         'hours_worked' => $hoursWorked,
    //     ]);
    //     return (int)$this->db->insert_id();
    // }

    // regularHours and overtimeHours are strings (e.g. "8", "8.5", "PH", "F")
    public function insert_detail($timesheetId, $workDate, $regularHours = null, $overtimeHours = null)
    {
        $this->db->insert(db_prefix() . 'timesheet_details', [
            'timesheet_id'   => (int)$timesheetId,
            'work_date'      => $workDate,
            'regular_hours'  => $regularHours,
            'overtime_hours' => $overtimeHours,
        ]);
        return (int)$this->db->insert_id();
    }

    // public function get_details($timesheet_id)
    // {
    //     $this->db->select('id, work_date, hours_worked');
    //     $this->db->from(db_prefix() . 'timesheet_details');
    //     $this->db->where('timesheet_id', $timesheet_id);
    //     $this->db->order_by('work_date', 'ASC');
    //     $query = $this->db->get();
    //     return $query->result_array();
    // }

    public function get_details($timesheet_id)
    {
        $this->db->select('id, work_date, regular_hours, overtime_hours');
        $this->db->from(db_prefix() . 'timesheet_details');
        $this->db->where('timesheet_id', $timesheet_id);
        $this->db->order_by('work_date', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_by_staff($staff_id)
    {
        $this->db->select('tm.*, p.name as project_name'); // select project name
        $this->db->from(db_prefix() . 'timesheet_master tm');
        $this->db->join(db_prefix() . 'projects p', 'p.id = tm.project_id', 'left'); // join projects table
        $this->db->where('tm.staff_id', (int)$staff_id);
        $this->db->order_by('tm.month_year', 'DESC'); // latest month first

        $query = $this->db->get();
        return $query->result_array();
    }

    // public function update_timesheet_detail($id, $data)
    // {
    //     $this->db->where('id', $id);
    //     return $this->db->update(db_prefix() . 'timesheet_details', $data);
    // }

    public function update_timesheet_detail($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'timesheet_details', $data);
    }

    // Returns all assignees for a project (projectassignee table)
    public function get_assignees_by_project($project_id)
    {
        $this->db->select('pa.*, s.staffid, s.firstname, s.lastname, s.name as full_name, s.iqama_number, s.work_hours_per_day, s.email, s.phonenumber');
        $this->db->from(db_prefix() . 'projectassignee pa');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = pa.staff_id', 'left');
        $this->db->where('pa.project_id', (int)$project_id);
        $this->db->order_by('s.firstname', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Returns master row (object or null) and details keyed by work_date
     * $month example: '2025-08'
     */
    public function get_timesheet_for_staff_month($project_id, $staff_id, $month)
    {
        $month_date = $month . '-01';
        $master = $this->db->get_where(db_prefix().'timesheet_master', [
            'project_id' => (int)$project_id,
            'staff_id'   => (int)$staff_id,
            'month_year' => $month_date
        ])->row();

        $details = [];
        if ($master) {
            $this->db->select('work_date, regular_hours, overtime_hours');
            $this->db->from(db_prefix().'timesheet_details');
            $this->db->where('timesheet_id', (int)$master->id);
            $res = $this->db->get()->result_array();
            foreach ($res as $r) {
                $details[$r['work_date']] = $r;
            }
        }
        return ['master' => $master, 'details' => $details];
    }

    public function get_unique_months_by_project($project_id)
    {
        $this->db->distinct();
        $this->db->select('month_year');
        $this->db->from(db_prefix() . 'timesheet_master');
        $this->db->where('project_id', (int)$project_id);
        $this->db->order_by('month_year', 'DESC'); // Newest first
        $query = $this->db->get();
        $months = $query->result_array();

        $formatted = [];
        foreach ($months as $m) {
            $date = new DateTime($m['month_year']);
            $formatted[] = [
                'month_year' => $m['month_year'],
                'formatted' => $date->format('F Y') // e.g., "September 2025"
            ];
        }
        return $formatted;
    }

    /**
     * Bulk create empty timesheet for all assignees in a project for a given month.
     * Inserts master (with 0 totals) and empty details for every date in the month.
     * Returns true on success, false on error.
     */
    public function create_empty_timesheet_for_month($project_id, $month)
    {
        // Get all assignees
        $assignees = $this->get_assignees_by_project($project_id);
        if (empty($assignees)) {
            return false;
        }

        $month_year = $month . '-01'; // e.g., '2025-09-01'
        $days_in_month = (int) date('t', strtotime($month_year)); // e.g., 30
        $start_date = $month_year;
        $end_date = date('Y-m-d', strtotime($month_year . ' + ' . ($days_in_month - 1) . ' days'));

        $this->db->trans_start(); // Use transaction for safety

        foreach ($assignees as $assignee) {
            $staff_id = $assignee['staff_id'];

            // Skip if already exists for this staff/month
            $exists = $this->db->get_where(db_prefix() . 'timesheet_master', [
                'staff_id' => $staff_id,
                'project_id' => $project_id,
                'month_year' => $month_year
            ])->row();
            if ($exists) {
                continue; // Or log warning; for now, skip to avoid duplicates
            }

            // Insert empty master
            $master_data = [
                'staff_id' => $staff_id,
                'project_id' => $project_id,
                'month_year' => $month_year,
                'total_hours' => 0.00,
                'total_regular_hours' => 0.00,
                'total_overtime_hours' => 0.00,
                'days_present' => 0,
                'fat' => null,
                'unit_price' => 0.00,
                'payable' => 0.00,
                'remarks' => null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $timesheet_id = $this->insert_master($master_data);

            // Insert empty details for each day
            $current_date = $start_date;
            while (strtotime($current_date) <= strtotime($end_date)) {
                $this->insert_detail($timesheet_id, $current_date, null, null);
                $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    
}
