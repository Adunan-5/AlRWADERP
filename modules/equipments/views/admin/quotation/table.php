<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'equipment_quotations.quotation_number as quotation_number',
    db_prefix() . 'equipment_quotations.quotation_date as quotation_date',
    db_prefix() . 'equipment_rfq.rfq_number as rfq_number',
    db_prefix() . 'suppliers.name as supplier_name',
    db_prefix() . 'equipment_quotations.total_amount as total_amount',
    db_prefix() . 'equipment_quotations.status as status',
    db_prefix() . 'equipment_quotations.valid_until_date as valid_until_date',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'equipment_quotations';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . $sTable . '.created_by',
    'LEFT JOIN ' . db_prefix() . 'suppliers ON ' . db_prefix() . 'suppliers.id = ' . $sTable . '.supplier_id',
    'LEFT JOIN ' . db_prefix() . 'equipment_rfq ON ' . db_prefix() . 'equipment_rfq.id = ' . $sTable . '.rfq_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
    db_prefix() . 'equipment_quotations.id',
    db_prefix() . 'equipment_quotations.rfq_id',
    db_prefix() . 'equipment_quotations.supplier_id',
    db_prefix() . 'equipment_quotations.currency',
    db_prefix() . 'equipment_rfq.project_reference',
    'CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as created_by_name',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Quotation Number (linked)
    $row[] = '<a href="' . admin_url('equipments/quotation/view/' . $aRow['id']) . '" class="tw-font-medium">
                ' . $aRow['quotation_number'] . '
              </a>';

    // Quotation Date
    $row[] = _d($aRow['quotation_date']);

    // RFQ Number (linked)
    if ($aRow['rfq_number']) {
        $row[] = '<a href="' . admin_url('equipments/rfq/view/' . $aRow['rfq_id']) . '">
                    ' . $aRow['rfq_number'] . '
                  </a>';
    } else {
        $row[] = '-';
    }

    // Supplier Name
    $row[] = $aRow['supplier_name'] ?: '-';

    // Total Amount
    $row[] = '<span class="tw-font-semibold">' . app_format_money($aRow['total_amount'], $aRow['currency']) . '</span>';

    // Status
    $status_class = '';
    switch ($aRow['status']) {
        case 'draft':
            $status_class = 'default';
            break;
        case 'submitted':
            $status_class = 'info';
            break;
        case 'under_review':
            $status_class = 'warning';
            break;
        case 'accepted':
            $status_class = 'success';
            break;
        case 'rejected':
            $status_class = 'danger';
            break;
        case 'expired':
            $status_class = 'default';
            break;
    }
    $row[] = '<span class="label label-' . $status_class . '">' . _l('quotation_status_' . $aRow['status']) . '</span>';

    // Valid Until Date
    $row[] = $aRow['valid_until_date'] ? _d($aRow['valid_until_date']) : '-';

    // Options
    $options = '';

    if (has_permission('equipment_quotation', '', 'view')) {
        $options .= '<a href="' . admin_url('equipments/quotation/view/' . $aRow['id']) . '" class="btn btn-default btn-icon">
                        <i class="fa fa-eye"></i>
                     </a> ';
    }

    if (has_permission('equipment_quotation', '', 'edit')) {
        $options .= '<a href="' . admin_url('equipments/quotation/edit/' . $aRow['id']) . '" class="btn btn-default btn-icon">
                        <i class="fa-regular fa-pen-to-square"></i>
                     </a> ';
    }

    if (has_permission('equipment_quotation', '', 'delete')) {
        $options .= '<a href="' . admin_url('equipments/quotation/delete/' . $aRow['id']) . '"
                        class="btn btn-danger btn-icon _delete"
                        data-toggle="tooltip"
                        title="' . _l('delete') . '">
                        <i class="fa fa-remove"></i>
                     </a>';
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}
