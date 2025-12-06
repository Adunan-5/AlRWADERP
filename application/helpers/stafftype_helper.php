<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all staff types stored in database
 *
 * @return array
 */
// function get_all_stafftypes()
// {
//     $types = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'stafftype')->result_array();

//     return hooks()->apply_filters('all_stafftypes', $types);
// }

if (!function_exists('get_all_stafftypes')) {
    function get_all_stafftypes() {
        $CI =& get_instance();
        return $CI->db->get(db_prefix() . 'stafftype')->result_array();
    }
}

/**
 * Get a single staff type row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_stafftype($id)
{
    $CI = &get_instance();

    $type = $CI->app_object_cache->get('db-stafftype-' . $id);

    if (!$type) {
        $CI->db->where('id', $id);
        $type = $CI->db->get(db_prefix() . 'stafftype')->row();
        $CI->app_object_cache->add('db-stafftype-' . $id, $type);
    }

    return hooks()->apply_filters('get_stafftype', $type);
}

/**
 * Get staff type name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_stafftype_name($id)
{
    $type = get_stafftype($id);
    if ($type) {
        return $type->name;
    }

    return '';
}
