<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'equipment_rfq.rfq_number as rfq_number',
    db_prefix() . 'equipment_rfq.rfq_date as rfq_date',
    db_prefix() . 'equipment_rfq.required_by_date as required_by_date',
    db_prefix() . 'equipment_rfq.project_reference as project_reference',
    db_prefix() . 'equipment_rfq.status as status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'equipment_rfq';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $sTable . '.created_by',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
    db_prefix() . 'equipment_rfq.id',
    '(SELECT COUNT(*) FROM ' . db_prefix() . 'equipment_rfq_items WHERE rfq_id = ' . db_prefix() . 'equipment_rfq.id) as items_count',
    '(SELECT COUNT(*) FROM ' . db_prefix() . 'equipment_rfq_suppliers WHERE rfq_id = ' . db_prefix() . 'equipment_rfq.id) as suppliers_count',
    'CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as created_by_name',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // RFQ Number with link
    $row[] = '<a href="' . admin_url('equipments/rfq/view/' . $aRow['id']) . '" class="tw-font-semibold">' . $aRow['rfq_number'] . '</a>';

    // RFQ Date
    $row[] = _d($aRow['rfq_date']);

    // Required By Date
    $row[] = $aRow['required_by_date'] ? _d($aRow['required_by_date']) : '-';

    // Project Reference
    $row[] = $aRow['project_reference'] ?: '-';

    // Status
    $status_labels = [
        'draft' => '<span class="label label-default">' . _l('rfq_status_draft') . '</span>',
        'sent' => '<span class="label label-info">' . _l('rfq_status_sent') . '</span>',
        'responses_received' => '<span class="label label-primary">' . _l('rfq_status_responses_received') . '</span>',
        'evaluated' => '<span class="label label-warning">' . _l('rfq_status_evaluated') . '</span>',
        'closed' => '<span class="label label-success">' . _l('rfq_status_closed') . '</span>',
        'cancelled' => '<span class="label label-danger">' . _l('rfq_status_cancelled') . '</span>',
    ];
    $row[] = isset($status_labels[$aRow['status']]) ? $status_labels[$aRow['status']] : $aRow['status'];

    // Items Count
    $row[] = '<span class="badge">' . $aRow['items_count'] . '</span>';

    // Suppliers Count
    $row[] = '<span class="badge">' . $aRow['suppliers_count'] . '</span>';

    // Created By
    $row[] = $aRow['created_by_name'];

    // Options
    $options = '';
    if (has_permission('equipment_rfq', '', 'view')) {
        $options .= '<a href="' . admin_url('equipments/rfq/view/' . $aRow['id']) . '" class="btn btn-default btn-icon btn-sm"><i class="fa fa-eye"></i></a> ';
    }
    if (has_permission('equipment_rfq', '', 'edit')) {
        $options .= '<a href="' . admin_url('equipments/rfq/edit/' . $aRow['id']) . '" class="btn btn-default btn-icon btn-sm"><i class="fa fa-edit"></i></a> ';
    }
    if (has_permission('equipment_rfq', '', 'delete')) {
        $options .= '<a href="' . admin_url('equipments/rfq/delete/' . $aRow['id']) . '" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-trash"></i></a>';
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}
