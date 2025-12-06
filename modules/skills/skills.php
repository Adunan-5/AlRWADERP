<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Skills
Description: Manage skills with English & Arabic names.
Version: 1.0.0
Author: Adunan
Requires at least: 2.3.*
*/

register_language_files('skills', ['skills']); // ensures language is loaded correctly

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('skills-menu', [
            'slug'     => 'skills-menu',
            'name'     => _l('skills'),
            'href'     => admin_url('skills'),
            'icon'     => 'fa fa-graduation-cap',
            'position' => 45,
        ]);
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('skills');
});

// hooks()->add_action('module_skills_activate', function () {
//     $CI = &get_instance();
//     $CI->load->library('migrations');
//     $CI->load->dbforge();

//     // Run migrations
//     $CI->migrations->set_path('modules/skills/migrations');
//     if ($CI->migrations->latest()) {
//         log_message('info', 'Skills module migrations completed successfully.');
//     } else {
//         log_message('error', 'Skills module migrations failed.');
//     }

//     // Run seeder
//     $CI->load->library('seeder');
//     $CI->seeder->call('Skills_seeder');
// });
