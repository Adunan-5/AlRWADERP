<?php

defined('BASEPATH') or exit('No direct script access allowed');

function get_custom_project_name_by_id($id)
{
    $CI =& get_instance();
    $CI->db->where('id', $id);
    $project = $CI->db->get(db_prefix() . 'projects')->row();
    return $project ? $project->name : '';
}

function get_equipment_name_by_id($id)
{
    $CI =& get_instance();
    $CI->db->where('id', $id);
    $equipment = $CI->db->get('tblequipments')->row(); // Adjust table name if needed
    return $equipment ? $equipment->name : '';
}