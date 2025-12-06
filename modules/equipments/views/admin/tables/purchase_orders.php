<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'po_number',
    db_prefix() . 'suppliers.name as supplier_name',
    'po_date',
    'delivery_date',
    'payment_terms_days',
    'status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'equipment_purchase_orders';

$join = [
    'LEFT JOIN ' . db_prefix() . 'suppliers ON ' . db_prefix() . 'suppliers.id = ' . db_prefix() . 'equipment_purchase_orders.supplier_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
    db_prefix() . 'equipment_purchase_orders.id',
    db_prefix() . 'equipment_purchase_orders.supplier_id',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // PO Number
    $row[] = '<a href="' . admin_url('equipments/purchase_orders/view/' . $aRow['id']) . '" class="tw-font-semibold">' . $aRow['po_number'] . '</a>';

    // Supplier
    $row[] = $aRow['supplier_name'] ?: '-';

    // PO Date
    $row[] = _d($aRow['po_date']);

    // Delivery Date
    $row[] = $aRow['delivery_date'] ? _d($aRow['delivery_date']) : '-';

    // Payment Terms
    $row[] = $aRow['payment_terms_days'] . ' ' . _l('days');

    // Status
    $status_labels = [
        'draft'              => '<span class="label label-default">' . _l('po_status_draft') . '</span>',
        'sent'               => '<span class="label label-info">' . _l('po_status_sent') . '</span>',
        'confirmed'          => '<span class="label label-primary">' . _l('po_status_confirmed') . '</span>',
        'partially_received' => '<span class="label label-warning">' . _l('po_status_partially_received') . '</span>',
        'received'           => '<span class="label label-success">' . _l('po_status_received') . '</span>',
        'cancelled'          => '<span class="label label-danger">' . _l('po_status_cancelled') . '</span>',
    ];
    $row[] = isset($status_labels[$aRow['status']]) ? $status_labels[$aRow['status']] : $aRow['status'];

    // Options column
    $options = '<div class="tw-flex tw-items-center tw-gap-2">';

    if (has_permission('equipment_purchase_orders', '', 'view')) {
        $options .= '<a href="' . admin_url('equipments/purchase_orders/view/' . $aRow['id']) . '" class="btn btn-default btn-xs" title="' . _l('view') . '">';
        $options .= '<i class="fa fa-eye"></i>';
        $options .= '</a>';
    }

    if (has_permission('equipment_purchase_orders', '', 'edit')) {
        $options .= '<a href="' . admin_url('equipments/purchase_orders/edit/' . $aRow['id']) . '" class="btn btn-default btn-xs" title="' . _l('edit') . '">';
        $options .= '<i class="fa fa-edit"></i>';
        $options .= '</a>';
    }

    if (has_permission('equipment_purchase_orders', '', 'delete')) {
        $options .= '<a href="#" class="btn btn-danger btn-xs delete-po" data-id="' . $aRow['id'] . '" title="' . _l('delete') . '">';
        $options .= '<i class="fa fa-trash"></i>';
        $options .= '</a>';
    }

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}
