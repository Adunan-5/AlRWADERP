<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'platenumber_code',
    'equipmenttype',
    'ownership_type',
    'phone',
    'email',
    'available_from_date',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'equipments';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if ($aColumns[$i] == 'name') {
            $_data = '<a href="' . admin_url('equipments/edit/' . $aRow['id']) . '" class="tw-font-medium">' . e($_data) . '</a>';

            $_data .= '<div class="row-options">';

            if (has_permission('equipments', '', 'view')) {
                $_data .= '<a href="' . admin_url('equipments/edit/' . $aRow['id']) . '">' . _l('view') . '</a>';
            }

            if (has_permission('equipments', '', 'edit')) {
                $_data .= ' | <a href="' . admin_url('equipments/edit/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }

            if (has_permission('equipments', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('equipments/delete/' . $aRow['id']) . '" class="_delete tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">' . _l('delete') . '</a>';
            }

            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'ownership_type') {
            if ($_data == 'Own') {
                $_data = '<span class="label label-success">' . _l('Own') . '</span>';
            } elseif ($_data == 'Rented Supplier') {
                $_data = '<span class="label label-primary">' . _l('Rented Supplier') . '</span>';
            } elseif ($_data == 'Rented Individuals') {
                $_data = '<span class="label label-info">' . _l('Rented Individuals') . '</span>';
            }
        } elseif ($aColumns[$i] == 'equipmenttype') {
            // Display equipment types (stored as comma-separated IDs)
            if (!empty($_data)) {
                $type_ids = explode(',', $_data);
                $type_names = [];
                foreach ($type_ids as $type_id) {
                    $type = get_equipment_type($type_id);
                    if ($type) {
                        $type_names[] = $type->name;
                    }
                }
                $_data = !empty($type_names) ? implode(', ', $type_names) : '-';
            } else {
                $_data = '-';
            }
        }

        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
