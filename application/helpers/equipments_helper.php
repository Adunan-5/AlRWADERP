<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all equipment stored in database
 *
 * @return array
 */
function get_all_equipments()
{
    $equipments = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'equipments')->result_array();

    return hooks()->apply_filters('all_equipments', $equipments);
}

/**
 * Get a single equipment row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_equipment($id)
{
    $CI = &get_instance();

    $equipment = $CI->app_object_cache->get('db-equipment-' . $id);

    if (!$equipment) {
        $CI->db->where('id', $id);
        $equipment = $CI->db->get(db_prefix() . 'equipments')->row();
        $CI->app_object_cache->add('db-equipment-' . $id, $equipment);
    }

    return hooks()->apply_filters('get_equipment', $equipment);
}

/**
 * Get equipment name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_equipment_name($id)
{
    $equipment = get_equipment($id);

    if ($equipment) {
        return $equipment->name;
    }

    return '';
}

/**
 * Get equipment platenumber_code by ID
 *
 * @param mixed $id
 * @return string
 */
function get_equipment_plate_number($id)
{
    $equipment = get_equipment($id);

    if ($equipment) {
        return $equipment->platenumber_code;
    }

    return '';
}

/**
 * Get equipment insurance expiry date by ID
 *
 * @param mixed $id
 * @return string
 */
function get_equipment_insurance_expiry($id)
{
    $equipment = get_equipment($id);

    if ($equipment) {
        return $equipment->insurance_expires_on;
    }

    return '';
}
