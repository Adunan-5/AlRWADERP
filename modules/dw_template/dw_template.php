<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Document Workflow Template
Description: Advanced document templates with placeholders and type-specific defaults
Version: 1.0.0
Author: Adunan
Requires at least: 2.3.*
*/

register_language_files('dw_template', ['dw_template']);

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('dw-template-menu', [
            'slug'     => 'dw-template-menu',
            'name'     => _l('dw_document_templates'),
            'href'     => admin_url('dw_template'),
            'icon'     => 'fa fa-file-text-o',
            'position' => 48,
        ]);
    }
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('dw_template');
});