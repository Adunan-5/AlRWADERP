<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff_timesheet_model extends App_Model
{
    public function get_grids_by_staff($staff_id)
    {
        $this->db->select('tm.id as timesheet_id, tm.project_id, p.name as project_name, tm.month_year, s.iqama_number, s.work_hours_per_day, CONCAT(s.firstname, " ", s.lastname) as staff_name');
        $this->db->from(db_prefix() . 'timesheet_master tm');
        $this->db->join(db_prefix() . 'projects p', 'p.id = tm.project_id', 'left');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = tm.staff_id', 'left');
        $this->db->where('tm.staff_id', $staff_id);
        $this->db->order_by('tm.month_year', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_details_by_timesheet($timesheet_id)
    {
        $this->db->select('id, work_date, regular_hours, overtime_hours');
        $this->db->from(db_prefix() . 'timesheet_details');
        $this->db->where('timesheet_id', $timesheet_id);
        $this->db->order_by('work_date', 'ASC');
        return $this->db->get()->result_array();
    }

    public function save_grid($data)
    {
        $timesheet_id = $data['timesheet_id'];
        $rows = json_decode($data['rows'], true);

        if (!$timesheet_id || !is_array($rows)) {
            return false;
        }

        $total_regular = 0;
        $total_overtime = 0;

        foreach ($rows as $row) {
            $work_date = $row['work_date'] ?? null;
            $regular_hours = $row['regular_hours'] ?? null;
            $overtime_hours = $row['overtime_hours'] ?? null;

            if (!$work_date) {
                continue; // skip invalid
            }

            $total_regular += is_numeric($regular_hours) ? $regular_hours : 0;
            $total_overtime += is_numeric($overtime_hours) ? $overtime_hours : 0;

            // check if exists
            $existing = $this->db->get_where(db_prefix() . 'timesheet_details', [
                'timesheet_id' => $timesheet_id,
                'work_date' => $work_date
            ])->row();

            if ($existing) {
                $this->db->where('id', $existing->id);
                $this->db->update(db_prefix() . 'timesheet_details', [
                    'regular_hours' => $regular_hours,
                    'overtime_hours' => $overtime_hours
                ]);
            } else {
                $this->db->insert(db_prefix() . 'timesheet_details', [
                    'timesheet_id' => $timesheet_id,
                    'work_date' => $work_date,
                    'regular_hours' => $regular_hours,
                    'overtime_hours' => $overtime_hours
                ]);
            }
        }

        // Update master totals so project section sees changes
        $this->db->where('id', $timesheet_id);
        $this->db->update(db_prefix() . 'timesheet_master', [
            'total_regular_hours' => $total_regular,
            'total_overtime_hours' => $total_overtime,
            'total_hours' => $total_regular + $total_overtime
        ]);

        return true;
    }
}
