<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Profession Types
Description: Manage Profession Type with English & Arabic names.
Version: 1.0.0
Author: Vasu
Requires at least: 2.3.*
*/

register_language_files('professiontype', ['professiontype']); // ensures language is loaded correctly

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('professiontype-menu', [
            'slug'     => 'professiontype-menu',
            'name'     => _l('profession_types'),
            'href'     => admin_url('professiontype'),
            'icon'     => 'fa fa-person-chalkboard',
            'position' => 45,
        ]);
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('professiontype');
});

// Register permissions for Profession Types
hooks()->add_filter('staff_permissions', function ($permissions) {
    $permissions['professiontype'] = [
        'name' => _l('profession_types'), // Label shown in Roles page
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    return $permissions;
});