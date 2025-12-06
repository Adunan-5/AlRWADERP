<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Suppliers
Description: Manage suppliers with English & Arabic names.
Version: 1.0.0
Author: Vasu
Requires at least: 2.3.*
*/

register_language_files('suppliers', ['suppliers']); // ensures language is loaded correctly

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('suppliers-menu', [
            'slug'     => 'suppliers-menu',
            'name'     => _l('suppliers'),
            'href'     => admin_url('suppliers'),
            'icon'     => 'fa fa-industry',
            'position' => 45,
        ]);
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('suppliers');
});

// Register permissions for Suppliers
hooks()->add_filter('staff_permissions', function ($permissions) {
    $permissions['suppliers'] = [
        'name' => _l('suppliers'), // Label shown in Roles page
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    return $permissions;
});