<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Equipments
Description: Comprehensive equipment rental management with operators, timesheets, and client billing
Version: 1.3.9
Author: Vasu / Enhanced by Claude
Requires at least: 2.3.*
*/

define('EQUIPMENTS_MODULE_NAME', 'equipments');
define('EQUIPMENTS_MODULE_UPLOAD_FOLDER', FCPATH . 'uploads/equipments/');

register_language_files('equipments', ['equipments']);

// Register activation hook to run migrations
register_activation_hook(EQUIPMENTS_MODULE_NAME, 'equipments_activation_hook');

function equipments_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

// Build menu structure
hooks()->add_action('admin_init', function () {
    $CI = &get_instance();

    if (is_staff_member()) {
        // Main Equipment menu with children
        $CI->app_menu->add_sidebar_menu_item('equipments-menu', [
            'slug'     => 'equipments-menu',
            'name'     => _l('equipments'),
            'icon'     => 'fa fa-truck',
            'position' => 45,
            'href'     => admin_url('equipments'),
        ]);

        // Equipments submenu
        if (has_permission('equipments', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'equipments-list',
                'name'     => _l('equipments'),
                'href'     => admin_url('equipments'),
                'icon'     => 'fa fa-wrench',
                'position' => 1,
            ]);
        }

        // Operators submenu
        if (has_permission('operators', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'operators-list',
                'name'     => _l('operators'),
                'href'     => admin_url('equipments/operators'),
                'icon'     => 'fa fa-users',
                'position' => 2,
            ]);
        }

        // Mobilization submenu
        if (has_permission('equipment_mobilization', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'mobilization-list',
                'name'     => _l('mobilization'),
                'href'     => admin_url('equipments/mobilization'),
                'icon'     => 'fa fa-location-arrow',
                'position' => 3,
            ]);
        }

        // Timesheets submenu
        if (has_permission('equipment_timesheets', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'timesheets-list',
                'name'     => _l('timesheets'),
                'href'     => admin_url('equipments/timesheets'),
                'icon'     => 'fa fa-calendar-check-o',
                'position' => 4,
            ]);
        }

        // Agreements submenu
        if (has_permission('equipment_agreements', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'agreements-list',
                'name'     => _l('agreements'),
                'href'     => admin_url('equipments/agreements'),
                'icon'     => 'fa fa-file-text-o',
                'position' => 5,
            ]);
        }

        // RFQ submenu (Request for Quotation)
        if (has_permission('equipment_rfq', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'rfq-list',
                'name'     => _l('rfq'),
                'href'     => admin_url('equipments/rfq'),
                'icon'     => 'fa fa-question-circle',
                'position' => 5.5,
            ]);
        }

        // Supplier Quotations submenu (Quotations from suppliers in response to RFQs)
        if (has_permission('equipment_quotation', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'supplier-quotations-list',
                'name'     => _l('supplier_quotations'),
                'href'     => admin_url('equipments/quotation'),
                'icon'     => 'fa fa-file-invoice',
                'position' => 5.7,
            ]);
        }

        // Purchase Orders submenu
        if (has_permission('equipment_purchase_orders', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'purchase-orders-list',
                'name'     => _l('purchase_orders'),
                'href'     => admin_url('equipments/purchase_orders'),
                'icon'     => 'fa fa-shopping-cart',
                'position' => 6,
            ]);
        }

        // Quotations submenu (Client)
        if (has_permission('equipment_quotations', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'quotations-list',
                'name'     => _l('quotations'),
                'href'     => admin_url('equipments/quotations'),
                'icon'     => 'fa fa-file-text',
                'position' => 7,
            ]);
        }

        // Sales Orders submenu
        if (has_permission('equipment_sales_orders', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'sales-orders-list',
                'name'     => _l('sales_orders'),
                'href'     => admin_url('equipments/sales_orders'),
                'icon'     => 'fa fa-briefcase',
                'position' => 7.5,
            ]);
        }

        // Document Types Settings
        if (is_admin()) {
            $CI->app_menu->add_sidebar_children_item('equipments-menu', [
                'slug'     => 'equipment-settings',
                'name'     => _l('settings'),
                'href'     => admin_url('equipments/settings'),
                'icon'     => 'fa fa-cog',
                'position' => 10,
            ]);
        }
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('equipments');
});

// Register permissions
hooks()->add_filter('staff_permissions', function ($permissions) {
    $permissions['equipments'] = [
        'name' => _l('equipments'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['operators'] = [
        'name' => _l('operators'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['equipment_mobilization'] = [
        'name' => _l('mobilization'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['equipment_timesheets'] = [
        'name' => _l('equipment_timesheets'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
            'approve' => _l('permission_approve'),
        ],
    ];

    $permissions['equipment_agreements'] = [
        'name' => _l('equipment_agreements'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['equipment_rfq'] = [
        'name' => _l('rfq'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['equipment_quotation'] = [
        'name' => _l('supplier_quotations'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['equipment_purchase_orders'] = [
        'name' => _l('equipment_purchase_orders'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['equipment_quotations'] = [
        'name' => _l('equipment_quotations'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['equipment_sales_orders'] = [
        'name' => _l('equipment_sales_orders'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $permissions;
});