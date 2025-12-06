<?php
header('Content-Type: application/json; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    's.name as staff_name', // 0
    'tm.total_hours',       // 1
    'tm.fat',               // 2
    'tm.days_present',      // 3
    'tm.unit_price',        // 4
    'tm.payable',           // 5
    'tm.month_year',           // 5
    'tm.remarks',           // 6
];

$sIndexColumn = 'tm.id';
$sTable       = db_prefix().'timesheet_master tm';

$join = [
    'JOIN ' . db_prefix() . 'staff s ON s.staffid = tm.staff_id',
];

$where = [];

if (isset($params['project_id']) && (int)$params['project_id'] > 0) {
    $where[] = 'AND tm.project_id = ' . (int)$params['project_id'];
}

if ($this->ci->input->get('month')) {
    $month = $this->ci->db->escape_str($this->ci->input->get('month'));
    $where[] = 'AND DATE_FORMAT(tm.month_year, "%Y-%m") = "' . $month . '"';
}

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    ['tm.id', 's.staffid'] // extra fields for links
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Staff link
    // $row[] = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '">' . e($aRow['staff_name']) . '</a>';
    $row[] = e($aRow['staff_name']);
    $row[] = e($aRow['total_hours']);
    $row[] = e($aRow['fat']);
    $row[] = e($aRow['days_present']);
    $row[] = e($aRow['unit_price']);
    $row[] = e($aRow['payable']);
    $row[] = e($aRow['remarks']);
    $row[] = date('F Y', strtotime($aRow['month_year']));

    // Options column
    $row[] = '<button type="button" class="btn btn-sm btn-default view-timesheet" data-id="' . $aRow['id'] . '">View</button>';

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;