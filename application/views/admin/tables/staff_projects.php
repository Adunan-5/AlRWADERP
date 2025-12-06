<?php

defined('BASEPATH') or exit('No direct script access allowed');

// $aColumns         = [db_prefix() . 'projects.name', 'start_date', 'deadline', 'status'];
// $sIndexColumn     = 'id';
// $sTable           = db_prefix() . 'projects';
// $additionalSelect = [db_prefix() . 'projects.id'];
// $join             = [
//     'JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'projects.clientid',
//     ];

// $where    = [];
// $staff_id = get_staff_user_id();
// if ($this->ci->input->post('staff_id')) {
//     $staff_id = $this->ci->input->post('staff_id');
// } else {
//     // Request from dashboard, finished and canceled not need to be shown
//     array_push($where, ' AND status != 4 AND status != 5');
// }

// array_push($where, ' AND ' . db_prefix() . 'projects.id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . $this->ci->db->escape_str($staff_id) . ')');

// $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

// $output  = $result['output'];
// $rResult = $result['rResult'];

// foreach ($rResult as $aRow) {
//     $row = [];
//     for ($i = 0 ; $i < count($aColumns) ; $i++) {
//         $_data = $aRow[ $aColumns[$i] ];

//         if ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'deadline') {
//             $_data = e(_d($_data));
//         } elseif ($aColumns[$i] == 'name') {
//             $_data = '<a href="' . admin_url('projects/view/' . $aRow['id']) . '">' . e($_data) . '</a>';
//         } elseif ($aColumns[$i] == 'status') {
//             $status = get_project_status_by_id($_data);
//             $status = '<span class="label label project-status-' . $_data . '" style="color:' . $status['color'] . ';border:1px solid ' . $status['color'] . '">' . e($status['name']) . '</span>';
//             $_data  = $status;
//         }

//         $row[] = $_data;
//     }
//     $output['aaData'][] = $row;
// }


$aColumns = [
    'project_id',
    'skills',
    'start_date',
    'end_date',
    'rate_type',
];

$sIndexColumn     = 'id';
$sTable           = db_prefix() . 'projectassignee';
$additionalSelect = [
    'id',
    'badge',
    'equipment_id',
    'regular_rate',
    'overtime_rate',
];
$join             = []; // No join needed unless you want client/project details

$where = [];
$staff_id = get_staff_user_id();

if ($this->ci->input->post('staff_id')) {
    $staff_id = $this->ci->input->post('staff_id');
}
$where[] = 'AND staff_id = ' . $this->ci->db->escape_str($staff_id);

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // For edit modal: fetch the missing fields manually
    $fullData = [
        'id'             => $aRow['id'],
        'project_id'     => $aRow['project_id'],
        'skills'         => $aRow['skills'],
        'start_date'     => $aRow['start_date'],
        'end_date'       => $aRow['end_date'],
        'rate_type'      => $aRow['rate_type'],
        'badge'          => $aRow['badge'] ?? '',
        'equipment_id'   => $aRow['equipment_id'] ?? '',
        'regular_rate'   => $aRow['regular_rate'] ?? '',
        'overtime_rate'  => $aRow['overtime_rate'] ?? '',
    ];

    foreach ($aColumns as $column) {
        $_data = $aRow[$column];

        if ($column == 'project_id') {
            // $_data = '<a href="#" class="edit-project" 
            //     data-id="' . $fullData['id'] . '"
            //     data-project="' . $fullData['project_id'] . '"
            //     data-skills="' . $fullData['skills'] . '"
            //     data-badge="' . $fullData['badge'] . '"
            //     data-start="' . $fullData['start_date'] . '"
            //     data-end="' . $fullData['end_date'] . '"
            //     data-equipment="' . $fullData['equipment_id'] . '"
            //     data-regular="' . $fullData['regular_rate'] . '"
            //     data-overtime="' . $fullData['overtime_rate'] . '"
            //     data-type="' . $fullData['rate_type'] . '"
            // >' . get_custom_project_name_by_id($aRow['project_id']) . '</a>';

            $_data = '<a href="' . admin_url('projects/view/' . $aRow['project_id'] . '?group=project_timesheets') . '">'
                        . get_custom_project_name_by_id($aRow['project_id']) .
                    '</a>';
        }

        if ($column == 'skills') {
            $skillNames = [];
            foreach (explode(',', $_data) as $id) {
                $skillName = get_skill_name(trim($id));
                if ($skillName) {
                    $skillNames[] = $skillName;
                }
            }
            $_data = implode(', ', $skillNames);
        }

        if (in_array($column, ['start_date', 'end_date'])) {
            $_data = _d($_data);
        }

        if ($column == 'rate_type') {
            $_data = $aRow['rate_type'] === 'hourly' ? 'Hourly' : ($aRow['rate_type'] === 'monthly' ? 'Monthly' : ucfirst($aRow['rate_type']));
        }

        $row[] = $_data;
    }

    // Action column
    $options = '<button class="btn btn-default btn-sm edit-job"
        data-id="' . $fullData['id'] . '"
        data-project="' . $fullData['project_id'] . '"
        data-skills="' . $fullData['skills'] . '"
        data-badge="' . $fullData['badge'] . '"
        data-start="' . $fullData['start_date'] . '"
        data-end="' . $fullData['end_date'] . '"
        data-equipment="' . $fullData['equipment_id'] . '"
        data-regular="' . $fullData['regular_rate'] . '"
        data-overtime="' . $fullData['overtime_rate'] . '"
        data-type="' . $fullData['rate_type'] . '">Edit</button>';

    $options .= ' <button class="btn btn-danger btn-sm delete-project-assignment"
        data-id="' . $fullData['id'] . '"
        data-project="' . get_custom_project_name_by_id($fullData['project_id']) . '">
        <i class="fa fa-trash"></i> Delete
        </button>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
