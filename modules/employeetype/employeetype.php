<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Employee Type
Description: Manage employee types
Version: 1.0.0
Author: Adunan
Requires at least: 2.3.*
*/

register_language_files('employeetype', ['employeetype']);

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('employeetype-menu', [
            'slug'     => 'employeetype-menu',
            'name'     => _l('employee_types'),
            'href'     => admin_url('employeetype'),
            'icon'     => 'fa fa-user-tag',
            'position' => 46,
        ]);
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('employeetype');
});

// Register permissions for Employee Type
hooks()->add_filter('staff_permissions', function ($permissions) {
    $permissions['employeetype'] = [
        'name' => _l('employee_types'), // label shown in Roles page
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    return $permissions;
});
