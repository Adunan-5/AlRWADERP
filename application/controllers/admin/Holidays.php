<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Holidays extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('holidays_model');
    }

    /**
     * List all holidays
     */
    public function index()
    {
        if (!is_admin()) {
            access_denied('Holidays');
        }

        $data['title'] = _l('holidays');
        $this->load->view('admin/holidays/manage', $data);
    }

    /**
     * Get holidays for DataTables
     */
    public function table()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }

        $this->app->get_table_data('holidays');
    }

    /**
     * Get single holiday (AJAX)
     */
    public function get($id)
    {
        if (!is_admin()) {
            ajax_access_denied();
        }

        $holiday = $this->holidays_model->get($id);
        echo json_encode($holiday);
    }

    /**
     * Add/Update holiday
     */
    public function save()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }

        // Set JSON header
        header('Content-Type: application/json');

        $id = $this->input->post('id');
        $data = [
            'label' => $this->input->post('label'),
            'holiday_date' => $this->input->post('holiday_date'),
            'description' => $this->input->post('description')
        ];

        // Check if date already exists
        if ($this->holidays_model->date_exists($data['holiday_date'], $id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('holiday_date_already_exists')
            ]);
            exit;
        }

        if ($id) {
            // Update
            $success = $this->holidays_model->update($id, $data);
            $message = $success ? _l('updated_successfully', _l('holiday')) : _l('something_went_wrong');
        } else {
            // Insert
            $id = $this->holidays_model->add($data);
            $success = $id > 0;
            $message = $success ? _l('added_successfully', _l('holiday')) : _l('something_went_wrong');
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    /**
     * Delete holiday
     */
    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Holidays');
        }

        $success = $this->holidays_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('holiday')));
        } else {
            set_alert('danger', _l('something_went_wrong'));
        }

        redirect(admin_url('holidays'));
    }

    /**
     * Get holiday dates for AJAX (used by timesheet grid)
     */
    public function get_dates()
    {
        $dates = $this->holidays_model->get_holiday_dates();
        echo json_encode(['success' => true, 'dates' => $dates]);
    }
}
