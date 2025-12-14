<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'label',
    'holiday_date',
    'description',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'holidays';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if ($aColumns[$i] == 'label') {
            $_data = e($_data);
        } elseif ($aColumns[$i] == 'holiday_date') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'description') {
            $_data = e($_data);
        }

        $row[] = $_data;
    }

    // Options column
    $options = '<div class="tw-flex tw-items-center tw-space-x-2">';
    if (is_admin()) {
        $options .= '<a href="#" onclick="edit_holiday(' . $aRow['id'] . '); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
            <i class="fa-regular fa-pen-to-square fa-lg"></i>
        </a>';
        $options .= '<a href="' . admin_url('holidays/delete/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
            <i class="fa-regular fa-trash-can fa-lg"></i>
        </a>';
    }
    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
