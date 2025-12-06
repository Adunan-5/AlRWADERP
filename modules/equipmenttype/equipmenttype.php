<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Equipment Types
Description: Manage Equipment Type with English & Arabic names.
Version: 1.0.0
Author: Vasu
Requires at least: 2.3.*
*/

register_language_files('equipmenttype', ['equipmenttype']); // ensures language is loaded correctly

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('equipmenttype-menu', [
            'slug'     => 'equipmenttype-menu',
            'name'     => _l('equipment_types'),
            'href'     => admin_url('equipmenttype'),
            'icon'     => 'fa fa-screwdriver-wrench',
            'position' => 45,
        ]);
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('equipmenttype');
});

hooks()->add_filter('staff_permissions', 'equipmenttype_permissions');

function equipmenttype_permissions($permissions)
{
    $permissions['equipmenttype'] = [
        'name' => _l('equipment_types'), // name displayed in Roles table
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $permissions;
}

// Hide unwanted features and trim Projects capabilities
hooks()->add_filter('staff_permissions', function($permissions) {

    // List of features to hide completely
    $hidden_features = [
        'bulk_pdf_exporter', 'contracts', 'credit_notes', 'email_templates', 
        'estimates', 'expenses', 'items', 'knowledge_base', 
        'payments', 'proposals', 'reports', 'settings', 'subscriptions', 
        'tasks', 'checklist_templates', 'estimate_request', 'leads', 
        'document', 'goals', 'manufacturing', 'purchase_items', 
        'purchase_vendors', 'purchase_vendor_items', 'purchase_request', 
        'purchase_quotations', 'purchase_orders', 'purchase_order_return', 
        'purchase_contracts', 'purchase_invoices', 'purchase_debit_notes', 
        'purchase_reports', 'purchase_settings', 
        'change_approval_purchase_orders', 
        'change_approval_purchase_quotations', 
        'change_approval_purchase_requests', 'surveys',
        'inventory_items', 'inventory_receiving_voucher', 
        'inventory_delivery_voucher', 'inventory_delivery_voucher_change_serial', 
        'packing_lists', 'internal_delivery_note', 'loss_adjustment', 
        'receiving_exporting_return_order', 'warehouse', 
        'inventory_history', 'inventory_report', 'inventory_setting',
    ];

    foreach ($hidden_features as $feature) {
        if (isset($permissions[$feature])) {
            unset($permissions[$feature]);
        }
    }

    // Restrict Projects module capabilities
    if (isset($permissions['projects'])) {
        $permissions['projects']['capabilities'] = [
            'view_own' => _l('permission_view_own'),
            'view'     => _l('permission_view'),
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ];
    }

    return $permissions;
});

