<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');

$staff_id = $params['staff_id'];

$aColumns = [
    'document_type',
    'caption',
    'file_name',
    'uploaded_at',
];

$sIndexColumn = 'id';
$sTable       = 'tblstaff_files';

$where   = [];
$join    = [];

array_push($where, 'AND staff_id=' . $staff_id);

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id','file_path']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['document_type'];
    $row[] = $aRow['caption'];
    $row[] = $aRow['file_name'];
    $row[] = _dt($aRow['uploaded_at']); // formatted datetime

    $options = '<div class="dropdown">
                    <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="#" class="edit-file" data-id="'.$aRow['id'].'"><i class="fa fa-edit"></i> '._l('edit').'</a></li>
                        <li><a href="'.admin_url('staff/download_file/'.$aRow['id']).'"><i class="fa fa-download"></i> '._l('download').'</a></li>
                        <li><a href="#" class="delete-file tw-text-danger" data-id="'.$aRow['id'].'" onclick="deleteStaffFile('.$aRow['id'].'); return false;"><i class="fa fa-trash"></i> '._l('delete').'</a></li>
                    </ul>
                </div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}

echo json_encode($output);
exit;
