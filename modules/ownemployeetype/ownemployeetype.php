<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Own Employee Types
Description: Manage Own Employee Type with English & Arabic names.
Version: 1.0.0
Author: Vasu
Requires at least: 2.3.*
*/

register_language_files('ownemployeetype', ['ownemployeetype']); // ensures language is loaded correctly

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    // if (is_staff_member()) {
    //     $CI->app_menu->add_sidebar_menu_item('ownemployeetype-menu', [
    //         'slug'     => 'ownemployeetype-menu',
    //         'name'     => _l('ownemployee_types'),
    //         'href'     => admin_url('ownemployeetype'),
    //         'icon'     => 'fa fa-address-card',
    //         'position' => 45,
    //     ]);
    // }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('ownemployeetype');
});

// Register permissions
hooks()->add_filter('staff_permissions', function ($permissions) {
    $permissions['ownemployeetype'] = [
        'name' => _l('ownemployee_types'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    return $permissions;
});