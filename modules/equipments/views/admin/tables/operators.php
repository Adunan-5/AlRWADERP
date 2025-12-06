<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'name_arabic',
    '(SELECT short_name FROM ' . db_prefix() . 'countries WHERE country_id = ' . db_prefix() . 'operators.nationality) as nationality',
    '(SELECT document_number FROM ' . db_prefix() . 'operator_documents WHERE operator_id = ' . db_prefix() . 'operators.id AND document_type_id = 1 LIMIT 1) as iqama_number',
    '(SELECT document_number FROM ' . db_prefix() . 'operator_documents WHERE operator_id = ' . db_prefix() . 'operators.id AND document_type_id = 3 LIMIT 1) as license_number',
    'operator_type',
    '(SELECT name FROM ' . db_prefix() . 'suppliers WHERE id = ' . db_prefix() . 'operators.supplier_id) as supplier_name',
    'is_active',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'operators';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['created_at']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {
        // Extract alias from column definition (e.g., "... as supplier_name" -> "supplier_name")
        $columnKey = $aColumns[$i];
        if (strpos($columnKey, ' as ') !== false) {
            $parts = explode(' as ', $columnKey);
            $columnKey = trim($parts[1]);
        }

        $_data = $aRow[$columnKey];

        if ($aColumns[$i] == 'name') {
            $_data = '<a href="' . admin_url('equipments/operator/' . $aRow['id']) . '" class="tw-font-medium">' . e($_data) . '</a>';

            $_data .= '<div class="row-options">';

            if (has_permission('operators', '', 'view')) {
                $_data .= '<a href="' . admin_url('equipments/operator/' . $aRow['id']) . '">' . _l('view') . '</a>';
            }

            if (has_permission('operators', '', 'edit')) {
                $_data .= ' | <a href="' . admin_url('equipments/operator/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }

            if (has_permission('operators', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('equipments/delete_operator/' . $aRow['id']) . '" class="_delete tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">' . _l('delete') . '</a>';
            }

            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'name_arabic') {
            $_data = '<span dir="rtl">' . e($_data) . '</span>';
        } elseif ($aColumns[$i] == 'operator_type') {
            if ($_data == 'own') {
                $_data = '<span class="label label-success">' . _l('own_operator') . '</span>';
            } else {
                $_data = '<span class="label label-primary">' . _l('hired_operator') . '</span>';
            }
        } elseif ($aColumns[$i] == 'is_active') {
            $checked = $_data == 1 ? 'checked' : '';

            $_data = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('change_operator_status') . '">
                <input type="checkbox"
                       data-switch-url="' . admin_url('equipments/toggle_operator_status') . '"
                       name="onoffswitch"
                       class="onoffswitch-checkbox"
                       id="operator_' . $aRow['id'] . '"
                       data-id="' . $aRow['id'] . '"
                       ' . $checked . '
                       ' . (!has_permission('operators', '', 'edit') ? 'disabled' : '') . '>
                <label class="onoffswitch-label" for="operator_' . $aRow['id'] . '"></label>
            </div>';

            // For exporting
            $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('active') : _l('inactive')) . '</span>';
        }

        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
