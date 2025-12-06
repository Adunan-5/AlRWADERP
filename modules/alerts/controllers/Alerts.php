<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alerts extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // if (!is_admin()) {
        //     access_denied('Alerts');
        // }
        $this->load->model('alerts/Alerts_model');

        // Only allow staff with 'view' permission
        if (!staff_can('view', 'alerts')) {
            access_denied('alerts');
        }
    }

    public function index()
{
    $months = (int) $this->input->get('months') ?: 1;
    $data['title']  = _l('alerts');
    $data['months'] = $months;
    $data['counts'] = $this->Alerts_model->get_alert_counts($months);

    if ($this->input->get('ajax')) {
        // return only the icons partial
        $this->load->view('alerts/admin/_icons', $data);
        return;
    }

    $this->load->view('alerts/admin/index', $data);
}

    public function view($type)
    {
        $months = (int) $this->input->get('months') ?: 1;
        $data['title']  = ucfirst(str_replace('_', ' ', $type)) . ' Alerts';
        $data['alerts'] = $this->Alerts_model->get_alert_details($type, $months);
        $data['type']   = $type;
        $this->load->view('alerts/admin/view', $data);
    }
}
