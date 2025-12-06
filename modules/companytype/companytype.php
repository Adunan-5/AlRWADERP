<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Company Types
Description: Manage Company Type with English & Arabic names.
Version: 1.0.0
Author: Vasu
Requires at least: 2.3.*
*/

register_language_files('companytype', ['companytype']); // ensures language is loaded correctly

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('companytype-menu', [
            'slug'     => 'companytype-menu',
            'name'     => _l('company_types'),
            'href'     => admin_url('companytype'),
            'icon'     => 'fa fa-building',
            'position' => 45,
        ]);
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('companytype');
});

// Register Companytype permissions
hooks()->add_filter('staff_permissions', 'companytype_permissions');

function companytype_permissions($permissions)
{
    $permissions['companytype'] = [
        'name' => _l('company_types'), // Name shown in Roles/Permissions
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $permissions;
}