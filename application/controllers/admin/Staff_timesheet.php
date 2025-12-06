<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff_timesheet extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_timesheet_model');
    }

    // Fetch all grids (projects + months) for one staff
    public function get_grids($staff_id)
    {
        $data = $this->staff_timesheet_model->get_grids_by_staff($staff_id);
        echo json_encode($data);
    }

    // Fetch detailed daily data for one grid (one timesheet_id)
    public function get_details($timesheet_id)
    {
        $details = $this->staff_timesheet_model->get_details_by_timesheet($timesheet_id);
        echo json_encode($details);
    }

    // Save edited grid
    public function save_timesheet()
    {
        $data = $this->input->post();
        $result = $this->staff_timesheet_model->save_grid($data);
        echo json_encode(['success' => $result]);
    }
}
