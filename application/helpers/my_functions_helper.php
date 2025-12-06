<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This function triggers the admin_init action hook.
 */
function my_custom_admin_init()
{
    do_action('admin_init');
}

/**
 * Hook into admin_init to add custom sidebar menu item.
 */
// hooks()->add_action('admin_init', function () {
//     $CI = &get_instance();

//     // Add "Staff Members" menu to main sidebar
//     $CI->app_menu->add_sidebar_menu_item('staff_direct', [
//         'name'     => _l('staff_members'),     // Menu label
//         'href'     => admin_url('staff'),      // Menu link
//         'icon'     => 'fa fa-user-circle',     // Font Awesome icon
//         'position' => 25,                      // Sidebar position (adjust as needed)
//     ]);
// });
