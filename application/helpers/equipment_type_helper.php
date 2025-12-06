<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all equipment_types stored in database
 *
 * @return array
 */
function get_all_equipment_types()
{
    $equipment_types = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'equipmenttype')->result_array();

    return hooks()->apply_filters('all_equipment_types', $equipment_types);
}

/**
 * Get a single equipment_type row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_equipment_type($id)
{
    $CI = &get_instance();

    $equipment_type = $CI->app_object_cache->get('db-equipmenttype-' . $id);

    if (!$equipment_type) {
        $CI->db->where('id', $id);
        $equipment_type = $CI->db->get(db_prefix() . 'equipmenttype')->row();
        $CI->app_object_cache->add('db-equipmenttype-' . $id, $equipment_type);
    }

    return hooks()->apply_filters('get_equipmenttype', $equipment_type);
}

/**
 * Get equipment_type name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_equipment_type_name($id)
{
    $equipment_type = get_equipment_type($id);
    if ($equipment_type) {
        return $equipment_type->name;
    }

    return '';
}

/**
 * Get equipment_type name (Arabic) by ID
 *
 * @param mixed $id
 * @return string
 */
function get_equipment_type_name_arabic($id)
{
    $equipment_type = get_equipment_type($id);
    if ($equipment_type) {
        return $equipment_type->name_arabic;
    }

    return '';
}
