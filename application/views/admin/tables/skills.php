<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['name', 'name_arabic'];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'skills';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['name'];
    $row[] = $aRow['name_arabic'];
    $row[] = '<a href="' . admin_url('skills/edit/' . $aRow['id']) . '" class="btn btn-sm btn-default">Edit</a>
              <a href="' . admin_url('skills/delete/' . $aRow['id']) . '" class="btn btn-sm btn-danger _delete">Delete</a>';

    $output['aaData'][] = $row;
}
