<?php

defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');

$staff_id = $params['staff_id'];

$aColumns = [
    'vacation_type',
    'start_date',
    // 'expected_end_date',
    'end_date',
    'comments',
    'status',
];

$sIndexColumn = 'id';
$sTable       = 'tblstaffvacations';

$where   = [];
$join    = [];

array_push($where, 'AND staff_id=' . $staff_id);

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = ucfirst($aRow['vacation_type']);
    $row[] = _d($aRow['start_date']);
    // $row[] = _d($aRow['expected_end_date']);
    $row[] = _d($aRow['end_date']);
    $row[] = $aRow['comments'];
    $row[] = ucfirst($aRow['status']);

    $options = '<button
                    class="btn btn-default btn-sm edit-vacation"
                    data-id="'.$aRow['id'].'">
                    <i class="fa fa-edit"></i> '._l('edit').'
                </button>';

    $options .= ' <button
                    class="btn btn-danger btn-sm delete-vacation"
                    data-id="'.$aRow['id'].'"
                    data-type="'.$aRow['vacation_type'].'">
                    <i class="fa fa-trash"></i> '._l('delete').'
                </button>';

    $row[] = $options;

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;