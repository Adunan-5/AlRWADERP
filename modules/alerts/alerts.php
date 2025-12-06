<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Alerts
Description: All staff related alerts
Version: 1.0.0
Author: Adunan
Requires at least: 2.3.*
*/
register_language_files('alerts', ['alerts']); // ensures language is loaded correctly

hooks()->add_action('admin_init', 'alerts_init_menu');

function alerts_init_menu()
{
    if (staff_can('view', 'alerts')) {
        $CI = &get_instance();
        $CI->app_menu->add_sidebar_menu_item('alerts', [
            'name'     => _l('alerts_menu'),
            'href'     => admin_url('alerts'),
            'icon'     => 'fa fa-bell',
            'position' => 15,
        ]);
    }
}

hooks()->add_action('app_admin_head', function () {
    load_admin_language('alerts');
});

hooks()->add_filter('staff_permissions', 'alerts_permissions');

function alerts_permissions($permissions)
{
    $permissions['alerts'] = [
        'name' => _l('alerts_menu'), // name shown in Roles/Permissions table
        'capabilities' => [
            'view' => _l('permission_view'), // only view is allowed
        ],
    ];

    return $permissions;
}
