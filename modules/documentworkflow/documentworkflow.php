<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Document Workflow
Description: Manage document templates and approvals
Version: 1.0.0
Author: Adunan
Requires at least: 2.3.*
*/

register_language_files('documentworkflow', ['documentworkflow']);

hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('documentworkflow-menu', [
            'slug'     => 'documentworkflow-menu',
            'name'     => _l('document_templates'),
            'href'     => admin_url('documentworkflow'),
            'icon'     => 'fa fa-file-text',
            'position' => 47,
        ]);
    }

    // Register permissions
    register_staff_capabilities('documentworkflow', [
        'view'   => _l('permission_view') . ' (' . _l('documentworkflow') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ]);
});

hooks()->add_action('app_admin_head', function () {
    load_admin_language('documentworkflow');
});

// Register Document workflow permissions
hooks()->add_filter('staff_permissions', 'documentworkflow_permissions');

function documentworkflow_permissions($permissions)
{
    $permissions['documentworkflow'] = [
        'name' => _l('document_templates'), // Name shown in Roles/Permissions
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $permissions;
}
