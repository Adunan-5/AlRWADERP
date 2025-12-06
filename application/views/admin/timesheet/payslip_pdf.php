<?php

defined('BASEPATH') or exit('No direct script access allowed');

// The $payslip variable is extracted from view_vars in build() method
$company_logo = get_option('company_logo');
$company_name = get_option('companyname');

$dimensions = $pdf->getPageDimensions();

// Header with company logo and title
$header = '<table cellpadding="5"><tr>';
$header .= '<td style="text-align:center;" width="100%">';
if (!empty($company_logo) && file_exists(FCPATH . 'uploads/company/' . $company_logo)) {
    $header .= '<img src="' . FCPATH . 'uploads/company/' . $company_logo . '" width="80"><br/>';
}
$header .= '<h2 style="margin:5px 0;">' . strtoupper($company_name) . '</h2>';
$header .= '<h3 style="margin:5px 0; border-bottom:2px solid #000; padding-bottom:5px;">Salary Slip</h3>';
$header .= '</td></tr></table>';

$pdf->writeHTML($header, true, false, true, false, '');
$pdf->ln(5);

// Employee Information Table
$info_table = '<table border="1" cellpadding="6" style="border-collapse:collapse;" width="100%">';
$info_table .= '<tr>';
$info_table .= '<td width="20%" style="background-color:#f0f0f0;"><strong>Period:</strong></td>';
$info_table .= '<td width="30%">' . $payslip['period'] . '</td>';
$info_table .= '<td width="20%" style="background-color:#f0f0f0;"><strong>Job Title:</strong></td>';
$info_table .= '<td width="30%">' . $payslip['job_title'] . '</td>';
$info_table .= '</tr>';
$info_table .= '<tr>';
$info_table .= '<td style="background-color:#f0f0f0;"><strong>Name:</strong></td>';
$info_table .= '<td>' . $payslip['employee_name'] . '</td>';
$info_table .= '<td style="background-color:#f0f0f0;"><strong>Iqama #:</strong></td>';
$info_table .= '<td>' . $payslip['iqama_number'] . '</td>';
$info_table .= '</tr>';
$info_table .= '<tr>';
$info_table .= '<td style="background-color:#f0f0f0;"><strong>Bank:</strong></td>';
$info_table .= '<td>' . $payslip['bank_name'] . '</td>';
$info_table .= '<td style="background-color:#f0f0f0;"><strong>Bank A/C #:</strong></td>';
$info_table .= '<td>' . $payslip['bank_account'] . '</td>';
$info_table .= '</tr>';
$info_table .= '<tr>';
$info_table .= '<td style="background-color:#f0f0f0;"><strong>Project:</strong></td>';
$info_table .= '<td colspan="3">' . $payslip['project_name'] . '</td>';
$info_table .= '</tr>';
$info_table .= '</table>';

$pdf->writeHTML($info_table, true, false, true, false, '');
$pdf->ln(8);

// Salary Breakdown Table
$salary_table = '<table border="1" cellpadding="8" style="border-collapse:collapse;" width="100%">';

// BASIC
$salary_table .= '<tr>';
$salary_table .= '<td width="70%" style="background-color:#f0f0f0;"><strong>BASIC</strong></td>';
$salary_table .= '<td width="30%" style="text-align:right;"><strong>' . number_format($payslip['basic'], 2) . '</strong> <small>SAR</small></td>';
$salary_table .= '</tr>';
$salary_table .= '<tr>';
$salary_table .= '<td colspan="2" style="padding-left:20px; font-size:12px; color:#555;">';
$salary_table .= '( ' . number_format($payslip['regular_hours'], 2) . ' HOURS X ' . number_format($payslip['hourly_rate'], 2) . ' <small>SAR</small> )';
$salary_table .= '</td>';
$salary_table .= '</tr>';

// OVERTIME (if exists)
if ($payslip['overtime_hours'] > 0) {
    $overtime_pay = $payslip['gross_salary'] - $payslip['basic'];
    $ot_rate = $overtime_pay / $payslip['overtime_hours'];

    $salary_table .= '<tr>';
    $salary_table .= '<td style="background-color:#f0f0f0;"><strong>OVERTIME</strong></td>';
    $salary_table .= '<td style="text-align:right;"><strong>' . number_format($overtime_pay, 2) . '</strong> <small>SAR</small></td>';
    $salary_table .= '</tr>';
    $salary_table .= '<tr>';
    $salary_table .= '<td colspan="2" style="padding-left:20px; font-size:12px; color:#555;">';
    $salary_table .= '( ' . number_format($payslip['overtime_hours'], 2) . ' HOURS X ' . number_format($ot_rate, 2) . ' <small>SAR</small> )';
    $salary_table .= '</td>';
    $salary_table .= '</tr>';
}

// GROSS SALARY
$salary_table .= '<tr>';
$salary_table .= '<td style="background-color:#f0f0f0;"><strong>GROSS SALARY</strong></td>';
$salary_table .= '<td style="text-align:right;"><strong>' . number_format($payslip['gross_salary'], 2) . '</strong> <small>SAR</small></td>';
$salary_table .= '</tr>';

// ALLOWANCES
if (!empty($payslip['allowances']) || $payslip['total_allowances'] > 0) {
    $salary_table .= '<tr>';
    $salary_table .= '<td style="background-color:#d4edda; color:#155724;"><strong>+ ALLOWANCES</strong></td>';
    $salary_table .= '<td style="text-align:right; background-color:#d4edda; color:#155724;"><strong>' . number_format($payslip['total_allowances'], 2) . '</strong> <small>SAR</small></td>';
    $salary_table .= '</tr>';

    // Allowances breakdown
    if (!empty($payslip['allowances'])) {
        foreach ($payslip['allowances'] as $allowance) {
            $salary_table .= '<tr>';
            $salary_table .= '<td style="padding-left:20px; font-size:12px; color:#555;">';
            $salary_table .= date('Y-m-d', strtotime($allowance['date'])) . ' - ' . htmlspecialchars($allowance['description']);
            $salary_table .= '</td>';
            $salary_table .= '<td style="text-align:right; font-size:12px; color:#28a745;">+' . number_format($allowance['amount'], 2) . ' <small>SAR</small></td>';
            $salary_table .= '</tr>';
        }
    }
}

// DEDUCTIONS
if (!empty($payslip['deductions']) || $payslip['total_deductions'] > 0) {
    $salary_table .= '<tr>';
    $salary_table .= '<td style="background-color:#f8d7da; color:#721c24;"><strong>- DEDUCTIONS</strong></td>';
    $salary_table .= '<td style="text-align:right; background-color:#f8d7da; color:#721c24;"><strong>' . number_format($payslip['total_deductions'], 2) . '</strong> <small>SAR</small></td>';
    $salary_table .= '</tr>';

    // Deductions breakdown
    if (!empty($payslip['deductions'])) {
        foreach ($payslip['deductions'] as $deduction) {
            $salary_table .= '<tr>';
            $salary_table .= '<td style="padding-left:20px; font-size:12px; color:#555;">';
            $salary_table .= date('Y-m-d', strtotime($deduction['date'])) . ' - ' . htmlspecialchars($deduction['description']);
            $salary_table .= '</td>';
            $salary_table .= '<td style="text-align:right; font-size:12px; color:#dc3545;">-' . number_format($deduction['amount'], 2) . ' <small>SAR</small></td>';
            $salary_table .= '</tr>';
        }
    }
}

// GRAND TOTAL
$salary_table .= '<tr>';
$salary_table .= '<td style="background-color:#f0f0f0;"><strong>GRAND TOTAL</strong></td>';
$salary_table .= '<td style="text-align:right;"><strong>' . number_format($payslip['grand_total'], 2) . '</strong> <small>SAR</small></td>';
$salary_table .= '</tr>';

$salary_table .= '</table>';

$pdf->writeHTML($salary_table, true, false, true, false, '');
$pdf->ln(10);

// Payment Details Section
$payment_section = '<div style="border-top:2px solid #000; padding-top:8px; margin-top:10px;">';
$payment_section .= '<strong style="font-size:14px;">PAYMENT DETAILS</strong><br/><br/>';

if (!empty($payslip['payments'])) {
    foreach ($payslip['payments'] as $payment) {
        $payment_section .= '<div style="background-color:#f9f9f9; padding:10px; margin-top:8px; border-radius:4px;">';
        $payment_section .= '<div style="font-size:11px; color:#555;">';
        $payment_section .= '<strong>PAID ON:</strong> ' . $payment['paid_date'] . ' | ';
        $payment_section .= '<strong>BY:</strong> ' . $payment['paid_by'] . ' | ';
        $payment_section .= '<strong>FROM:</strong> ' . $payment['paid_from'];
        $payment_section .= '</div>';
        $payment_section .= '<div style="text-align:right; margin-top:5px; font-size:13px;">';
        $payment_section .= '<strong style="color:#28a745;">' . number_format($payment['amount'], 2) . ' SAR</strong>';
        $payment_section .= '</div>';
        $payment_section .= '</div>';
    }

    // Show balance or paid badge
    if ($payslip['balance_to_pay'] > 0.01) {
        $payment_section .= '<div style="background-color:#fff3cd; padding:10px; margin-top:10px; border-radius:4px;">';
        $payment_section .= '<strong style="color:#856404;">BALANCE TO BE PAID:</strong>';
        $payment_section .= '<span style="float:right; color:#856404; font-size:14px;"><strong>' . number_format($payslip['balance_to_pay'], 2) . ' SAR</strong></span>';
        $payment_section .= '</div>';
    } else if ($payslip['is_paid']) {
        $payment_section .= '<div style="text-align:right; margin-top:10px;">';
        $payment_section .= '<span style="background-color:#5cb85c; color:#fff; padding:8px 20px; border-radius:4px; font-size:13px;"><strong>PAID</strong></span>';
        $payment_section .= '</div>';
    }
} else {
    $payment_section .= '<p style="color:#999; font-style:italic; margin:10px 0;">No payment records yet</p>';
}

$payment_section .= '</div>';
$pdf->writeHTML($payment_section, true, false, true, false, '');
$pdf->ln(5);

// Amount in Words
$amount_words = '<div style="background-color:#f9f9f9; padding:10px; border-left:3px solid #000; margin-top:10px;">';
$amount_words .= '<strong>Amount in words:</strong> ' . $payslip['amount_in_words'];
$amount_words .= '</div>';

$pdf->writeHTML($amount_words, true, false, true, false, '');
$pdf->ln(15);

// Signature Section
$signature = '<table width="100%" cellpadding="5">';
$signature .= '<tr>';
$signature .= '<td width="33%" style="text-align:center; vertical-align:bottom;">';
$signature .= '<div style="border-top:1px solid #000; margin-top:40px; padding-top:5px;">Employee Name<br/>' . $payslip['employee_name'] . '</div>';
$signature .= '</td>';
$signature .= '<td width="33%" style="text-align:center; vertical-align:bottom;">';
$signature .= '<div style="border-top:1px solid #000; margin-top:40px; padding-top:5px;">Prepared By:</div>';
$signature .= '</td>';
$signature .= '<td width="33%" style="text-align:center; vertical-align:bottom;">';
$signature .= '<div style="border-top:1px solid #000; margin-top:40px; padding-top:5px;">Approved By:</div>';
$signature .= '</td>';
$signature .= '</tr>';
$signature .= '</table>';

$pdf->writeHTML($signature, true, false, true, false, '');
