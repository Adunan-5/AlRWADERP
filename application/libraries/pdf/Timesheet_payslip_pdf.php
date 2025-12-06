<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Timesheet_payslip_pdf extends App_pdf
{
    protected $payslip_data;

    public function __construct($payslip_data)
    {
        parent::__construct();

        $this->payslip_data = $payslip_data;

        // Set title with employee name if available
        if (isset($payslip_data['employee_name'])) {
            $this->SetTitle('Salary Slip - ' . $payslip_data['employee_name']);
        } else {
            $this->SetTitle('Salary Slip');
        }
    }

    public function prepare()
    {
        // Set view vars using array format to match extract() in build()
        $this->set_view_vars([
            'payslip' => $this->payslip_data
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'timesheet_payslip';
    }

    protected function file_path()
    {
        $actualPath = APPPATH . 'views/admin/timesheet/payslip_pdf.php';
        return $actualPath;
    }
}
