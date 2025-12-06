<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'quotation_number',
    db_prefix() . 'clients.company as client_company',
    'quotation_date',
    'validity_date',
    'payment_terms_days',
    'status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'equipment_client_quotations';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'equipment_client_quotations.client_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
    db_prefix() . 'equipment_client_quotations.id',
    db_prefix() . 'equipment_client_quotations.client_id',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Quotation Number
    $row[] = '<a href="' . admin_url('equipments/quotations/view/' . $aRow['id']) . '" class="tw-font-semibold">' . $aRow['quotation_number'] . '</a>';

    // Client
    $row[] = $aRow['client_company'] ?: '-';

    // Quotation Date
    $row[] = _d($aRow['quotation_date']);

    // Validity Date
    $row[] = $aRow['validity_date'] ? _d($aRow['validity_date']) : '-';

    // Payment Terms
    $row[] = $aRow['payment_terms_days'] . ' ' . _l('days');

    // Status
    $status_labels = [
        'draft'    => '<span class="label label-default">' . _l('quotation_status_draft') . '</span>',
        'sent'     => '<span class="label label-info">' . _l('quotation_status_sent') . '</span>',
        'accepted' => '<span class="label label-success">' . _l('quotation_status_accepted') . '</span>',
        'rejected' => '<span class="label label-danger">' . _l('quotation_status_rejected') . '</span>',
        'expired'  => '<span class="label label-warning">' . _l('quotation_status_expired') . '</span>',
    ];
    $row[] = isset($status_labels[$aRow['status']]) ? $status_labels[$aRow['status']] : $aRow['status'];

    // Options
    $options = '';
    if (has_permission('equipment_quotations', '', 'view')) {
        $options .= '<a href="' . admin_url('equipments/quotations/view/' . $aRow['id']) . '" class="btn btn-default btn-icon btn-sm" data-toggle="tooltip" title="' . _l('view') . '"><i class="fa fa-eye"></i></a> ';
    }
    if (has_permission('equipment_quotations', '', 'edit')) {
        $options .= '<a href="' . admin_url('equipments/quotations/edit/' . $aRow['id']) . '" class="btn btn-default btn-icon btn-sm" data-toggle="tooltip" title="' . _l('edit') . '"><i class="fa fa-edit"></i></a> ';
    }
    if (has_permission('equipment_quotations', '', 'delete')) {
        $options .= '<a href="#" onclick="deleteQuotation(' . $aRow['id'] . '); return false;" class="btn btn-danger btn-icon btn-sm" data-toggle="tooltip" title="' . _l('delete') . '"><i class="fa fa-trash"></i></a>';
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}
