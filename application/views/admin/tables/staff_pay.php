<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');

$staff_id = $params['staff_id'];

$aColumns = [
    'start_date',
    'payout_type',
    'basic_pay',
    'overtime_pay',
    'food_allowance',
    'allowance',
    'fat_allowance',
    'accomodation_allowance',
    'mewa',
];

$sIndexColumn = 'id';
$sTable       = 'tblstaffpay';

$where   = [];
$join    = [];

array_push($where, 'AND staff_id=' . $staff_id);

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = _d($aRow['start_date']);
    $row[] = ucfirst($aRow['payout_type']);
    $row[] = $aRow['basic_pay'];
    $row[] = $aRow['overtime_pay'];
    $row[] = $aRow['food_allowance'];
    $row[] = $aRow['allowance'];
    $row[] = $aRow['fat_allowance'];
    $row[] = $aRow['accomodation_allowance'];
    $row[] = $aRow['mewa'];

    $options = '<button 
                    class="btn btn-default btn-sm edit-pay" 
                    data-id="'.$aRow['id'].'">
                    <i class="fa fa-edit"></i> '._l('edit').'
                </button>';

    $row[] = $options;

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;
