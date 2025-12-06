<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'order_number',
    db_prefix() . 'clients.company as client_company',
    'order_date',
    'expected_delivery_date',
    'total_amount',
    'status',
    'fulfillment_status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'equipment_sales_orders';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . $sTable . '.client_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
    db_prefix() . 'equipment_sales_orders.id',
    db_prefix() . 'equipment_sales_orders.client_id',
    db_prefix() . 'equipment_sales_orders.currency',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Order Number
    $row[] = '<a href="' . admin_url('equipments/sales_orders/view/' . $aRow['id']) . '" class="tw-font-semibold">' . $aRow['order_number'] . '</a>';

    // Client
    $row[] = $aRow['client_company'] ?: '-';

    // Order Date
    $row[] = _d($aRow['order_date']);

    // Expected Delivery Date
    $row[] = $aRow['expected_delivery_date'] ? _d($aRow['expected_delivery_date']) : '-';

    // Total Amount
    $row[] = app_format_money($aRow['total_amount'], $aRow['currency']);

    // Status
    $status_labels = [
        'draft'                => '<span class="label label-default">' . _l('so_status_draft') . '</span>',
        'confirmed'            => '<span class="label label-primary">' . _l('so_status_confirmed') . '</span>',
        'partially_fulfilled'  => '<span class="label label-warning">' . _l('so_status_partially_fulfilled') . '</span>',
        'fulfilled'            => '<span class="label label-success">' . _l('so_status_fulfilled') . '</span>',
        'cancelled'            => '<span class="label label-danger">' . _l('so_status_cancelled') . '</span>',
    ];
    $row[] = isset($status_labels[$aRow['status']]) ? $status_labels[$aRow['status']] : $aRow['status'];

    // Fulfillment Status
    $fulfillment_labels = [
        'pending'      => '<span class="label label-default">' . _l('fulfillment_pending') . '</span>',
        'in_progress'  => '<span class="label label-info">' . _l('fulfillment_in_progress') . '</span>',
        'completed'    => '<span class="label label-success">' . _l('fulfillment_completed') . '</span>',
        'cancelled'    => '<span class="label label-danger">' . _l('fulfillment_cancelled') . '</span>',
    ];
    $row[] = isset($fulfillment_labels[$aRow['fulfillment_status']]) ? $fulfillment_labels[$aRow['fulfillment_status']] : $aRow['fulfillment_status'];

    // Options
    $options = '<div class="tw-flex tw-items-center tw-gap-2">';

    if (has_permission('equipment_sales_orders', '', 'view')) {
        $options .= '<a href="' . admin_url('equipments/sales_orders/view/' . $aRow['id']) . '" class="btn btn-default btn-xs" title="' . _l('view') . '">';
        $options .= '<i class="fa fa-eye"></i>';
        $options .= '</a>';
    }

    if (has_permission('equipment_sales_orders', '', 'edit')) {
        $options .= '<a href="' . admin_url('equipments/sales_orders/edit/' . $aRow['id']) . '" class="btn btn-default btn-xs" title="' . _l('edit') . '">';
        $options .= '<i class="fa fa-edit"></i>';
        $options .= '</a>';
    }

    if (has_permission('equipment_sales_orders', '', 'delete')) {
        $options .= '<a href="#" class="btn btn-danger btn-xs delete-sales-order" data-id="' . $aRow['id'] . '" title="' . _l('delete') . '">';
        $options .= '<i class="fa fa-trash"></i>';
        $options .= '</a>';
    }

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}
