<?php

// defined('BASEPATH') or exit('No direct script access allowed');

// $has_permission_delete = staff_can('delete', 'staff');

// $custom_fields = get_custom_fields('staff', ['show_on_table' => 1]);

// $aColumns = [
//     'CAST(' . db_prefix() . 'staff.code AS UNSIGNED) as staff_code_order', // for sorting
//     db_prefix() . 'staff.code as staff_code', // Code
//     'ct.name as company_name',       // Company (joined below)
//     db_prefix() . 'staff.name as staff_name', // Name
//     db_prefix() . 'staff.iqama_number as iqama_number', // Iqama Number
//     db_prefix() . 'stafftype.name as stafftype_name',   // Employee Type
//     db_prefix() . 'staff.email as staff_email',         // Email
//     db_prefix() . 'staff.phonenumber as staff_phone',   // Phone Number
//     db_prefix() . 'staff.active as active',             // Active
// ];

// $sIndexColumn = 'staffid';
// $sTable       = db_prefix() . 'staff';

// $join   = [
//     'LEFT JOIN ' . db_prefix() . 'stafftype ON ' . db_prefix() . 'stafftype.id = ' . db_prefix() . 'staff.stafftype_id',
//     'LEFT JOIN ' . db_prefix() . 'companytype AS ct ON ct.id = ' . db_prefix() . 'staff.companytype_id',
// ];

// $i = 0;
// foreach ($custom_fields as $field) {
//     $select_as = 'cvalue_' . $i;
//     if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
//         $select_as = 'date_picker_cvalue_' . $i;
//     }
//     array_push($aColumns, 'ctable_' . $i . '.value as ' . $select_as);
//     array_push(
//         $join,
//         'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $i .
//         ' ON ' . db_prefix() . 'staff.staffid = ctable_' . $i . '.relid 
//           AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" 
//           AND ctable_' . $i . '.fieldid=' . $field['id']
//     );
//     $i++;
// }

// if (count($custom_fields) > 4) {
//     @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
// }

// $where = hooks()->apply_filters('staff_table_sql_where', []);

// // Exclude staffid 1 and 3
// $where[] = 'AND ' . db_prefix() . 'staff.staffid NOT IN (1,3)';

// $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
//     'profile_image',
//     'lastname',
//     'staffid',
// ]);

// $output  = $result['output'];
// $rResult = $result['rResult'];

// foreach ($rResult as $aRow) {
//     $row = [];

//     foreach ($aColumns as $col) {
//         if (strpos($col, ' as ') !== false) {
//             $alias = strafter($col, ' as ');
//             $_data = $aRow[$alias];
//         } else {
//             $_data = $aRow[$col];
//         }

//         switch ($col) {
//             case 'CAST(' . db_prefix() . 'staff.code AS UNSIGNED) as staff_code_order':
//                 // skip output (only used for sorting)
//                 continue 2;

//             case db_prefix() . 'staff.code as staff_code': // Code
//                 $_data = e($_data);
//                 break;

//             case 'companytype.name as company_name': // Company
//                 $_data = e($_data);
//                 break;

//             case db_prefix() . 'staff.name as staff_name': // Name
//                 // $profileImage = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '" target="_blank">' . staff_profile_image($aRow['staffid'], ['staff-profile-image-small']) . '</a>';

//                 // $fullName = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '" target="_blank" class="tw-font-medium">' . e($aRow['staff_name']) . '</a>';
//                 $profileImage = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], ['staff-profile-image-small']) . '</a>';

//                 $fullName = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '" class="tw-font-medium">' . e($aRow['staff_name']) . '</a>';

//                 $_data = $profileImage . ' ' . $fullName;

//                 $_data .= '<div class="row-options">';
//                 // $_data .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '"target="_blank">' . _l('view') . '</a>';
//                 $_data .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . _l('view') . '</a>';
//                 if (($has_permission_delete && !is_admin($aRow['staffid'])) || is_admin()) {
//                     if ($has_permission_delete && $output['iTotalRecords'] > 1 && $aRow['staffid'] != get_staff_user_id()) {
//                         $_data .= ' | <a href="#" onclick="delete_staff_member(' . $aRow['staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
//                     }
//                 }
//                 $_data .= '</div>';
//                 break;

//             case db_prefix() . 'staff.iqama_number as iqama_number': // Iqama Number
//                 $_data = e($_data);
//                 break;

//             case db_prefix() . 'stafftype.name as stafftype_name': // Employee Type
//                 $_data = e($_data);
//                 break;

//             case db_prefix() . 'staff.email as staff_email': // Email
//                 $_data = '<a href="mailto:' . e($_data) . '">' . e($_data) . '</a>';
//                 break;

//             case db_prefix() . 'staff.phonenumber as staff_phone': // Phone
//                 $_data = e($_data);
//                 break;

//             case db_prefix() . 'staff.active as active': // Active
//                 $checked = $aRow['active'] == 1 ? 'checked' : '';
//                 $_data   = '<div class="onoffswitch">
//                     <input type="checkbox" ' . (($aRow['staffid'] == get_staff_user_id() || (is_admin($aRow['staffid']) || staff_cant('edit', 'staff')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['staffid'] . '" data-id="' . $aRow['staffid'] . '" ' . $checked . '>
//                     <label class="onoffswitch-label" for="c_' . $aRow['staffid'] . '"></label>
//                 </div>';
//                 $_data  .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
//                 break;

//             default:
//                 if (strpos($col, 'date_picker_') !== false) {
//                     $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
//                 }
//                 break;
//         }

//         $row[] = $_data;
//     }

//     $row['DT_RowClass'] = 'has-row-options';
//     $row                = hooks()->apply_filters('staff_table_row', $row, $aRow);

//     $output['aaData'][] = $row;
// }


defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = staff_can('delete', 'staff');

$custom_fields = get_custom_fields('staff', ['show_on_table' => 1]);

$aColumns = [
    db_prefix() . 'staff.code as staff_code', // Code
    'ct.name as company_name',       // Company (joined below)
    db_prefix() . 'staff.name as staff_name', // Name
    db_prefix() . 'staff.iqama_number as iqama_number', // Iqama Number
    db_prefix() . 'stafftype.name as stafftype_name',   // Employee Type
    db_prefix() . 'staff.email as staff_email',         // Email
    db_prefix() . 'staff.phonenumber as staff_phone',   // Phone Number
    db_prefix() . 'staff.active as active',             // Active
];

$sIndexColumn = 'staffid';
$sTable       = db_prefix() . 'staff';

$join   = [
    'LEFT JOIN ' . db_prefix() . 'stafftype ON ' . db_prefix() . 'stafftype.id = ' . db_prefix() . 'staff.stafftype_id',
    'LEFT JOIN ' . db_prefix() . 'companytype AS ct ON ct.id = ' . db_prefix() . 'staff.companytype_id',
];

$i = 0;
foreach ($custom_fields as $field) {
    $select_as = 'cvalue_' . $i;
    if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
        $select_as = 'date_picker_cvalue_' . $i;
    }
    array_push($aColumns, 'ctable_' . $i . '.value as ' . $select_as);
    array_push(
        $join,
        'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $i .
        ' ON ' . db_prefix() . 'staff.staffid = ctable_' . $i . '.relid 
          AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" 
          AND ctable_' . $i . '.fieldid=' . $field['id']
    );
    $i++;
}

if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$where = hooks()->apply_filters('staff_table_sql_where', []);

// Exclude staffid 1 and 3
$where[] = 'AND ' . db_prefix() . 'staff.staffid NOT IN (1,3)';

// Staff Type Filter
if ($this->ci->input->get('stafftype') !== null) {
    $where[] = 'AND ' . db_prefix() . 'staff.stafftype_id = ' . (int)$this->ci->input->get('stafftype');
}

// Country Filter
if ($this->ci->input->get('country') !== null) {
    $where[] = 'AND ' . db_prefix() . 'staff.country = ' . (int)$this->ci->input->get('country');
}

// GOSI Filter
if ($this->ci->input->get('gosi') !== null) {
    $where[] = 'AND ' . db_prefix() . 'staff.has_GOSI = ' . (int)$this->ci->input->get('gosi');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'profile_image',
    'lastname',
    'staffid',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    foreach ($aColumns as $col) {
        if (strpos($col, ' as ') !== false) {
            $alias = strafter($col, ' as ');
            $_data = $aRow[$alias];
        } else {
            $_data = $aRow[$col];
        }

        switch ($col) {
            case db_prefix() . 'staff.code as staff_code': // Code
                $_data = e($_data);
                break;

            case 'ct.name as company_name': // Company
                $_data = e($_data);
                break;

            case db_prefix() . 'staff.name as staff_name': // Name
                // $profileImage = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '" target="_blank">' . staff_profile_image($aRow['staffid'], ['staff-profile-image-small']) . '</a>';

                // $fullName = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '" target="_blank" class="tw-font-medium">' . e($aRow['staff_name']) . '</a>';
                $profileImage = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], ['staff-profile-image-small']) . '</a>';

                $fullName = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '" class="tw-font-medium">' . e($aRow['staff_name']) . '</a>';

                $_data = $profileImage . ' ' . $fullName;

                $_data .= '<div class="row-options">';
                // $_data .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '"target="_blank">' . _l('view') . '</a>';
                $_data .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . _l('edit') . '</a>';
                if (($has_permission_delete && !is_admin($aRow['staffid'])) || is_admin()) {
                    if ($has_permission_delete && $output['iTotalRecords'] > 1 && $aRow['staffid'] != get_staff_user_id()) {
                        $_data .= ' | <a href="#" onclick="delete_staff_member(' . $aRow['staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
                    }
                }
                $_data .= '</div>';
                break;

            case db_prefix() . 'staff.iqama_number as iqama_number': // Iqama Number
                $_data = e($_data);
                break;

            case db_prefix() . 'stafftype.name as stafftype_name': // Employee Type
                $_data = e($_data);
                break;

            case db_prefix() . 'staff.email as staff_email': // Email
                $_data = '<a href="mailto:' . e($_data) . '">' . e($_data) . '</a>';
                break;

            case db_prefix() . 'staff.phonenumber as staff_phone': // Phone
                $_data = e($_data);
                break;

            case db_prefix() . 'staff.active as active': // Active
                $checked = $aRow['active'] == 1 ? 'checked' : '';
                $_data   = '<div class="onoffswitch">
                    <input type="checkbox" ' . (($aRow['staffid'] == get_staff_user_id() || (is_admin($aRow['staffid']) || staff_cant('edit', 'staff')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['staffid'] . '" data-id="' . $aRow['staffid'] . '" ' . $checked . '>
                    <label class="onoffswitch-label" for="c_' . $aRow['staffid'] . '"></label>
                </div>';
                $_data  .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
                break;

            default:
                if (strpos($col, 'date_picker_') !== false) {
                    $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
                }
                break;
        }

        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $row                = hooks()->apply_filters('staff_table_row', $row, $aRow);

    $output['aaData'][] = $row;
}