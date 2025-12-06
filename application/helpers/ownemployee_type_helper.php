<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all ownemployee_types stored in database
 *
 * @return array
 */
// function get_all_ownemployee_types()
// {
//     $ownemployee_types = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'ownemployeetype')->result_array();

//     return hooks()->apply_filters('all_ownemployee_types', $ownemployee_types);
// }

if (!function_exists('get_all_ownemployee_types')) {
    function get_all_ownemployee_types()
    {
        $CI = &get_instance();
        $CI->db->order_by('name', 'asc');
        return $CI->db->get(db_prefix() . 'ownemployeetype')->result_array();
    }
}

/**
 * Get a single ownemployee_type row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_ownemployee_type($id)
{
    $CI = &get_instance();

    $ownemployee_type = $CI->app_object_cache->get('db-ownemployeetype-' . $id);

    if (!$ownemployee_type) {
        $CI->db->where('id', $id);
        $ownemployee_type = $CI->db->get(db_prefix() . 'ownemployeetype')->row();
        $CI->app_object_cache->add('db-ownemployeetype-' . $id, $ownemployee_type);
    }

    return hooks()->apply_filters('get_ownemployeetype', $ownemployee_type);
}

/**
 * Get ownemployee_type name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_ownemployee_type_name($id)
{
    $ownemployee_type = get_ownemployee_type($id);
    if ($ownemployee_type) {
        return $ownemployee_type->name;
    }

    return '';
}

/**
 * Get ownemployee_type name (Arabic) by ID
 *
 * @param mixed $id
 * @return string
 */
function get_ownemployee_type_name_arabic($id)
{
    $ownemployee_type = get_ownemployee_type($id);
    if ($ownemployee_type) {
        return $ownemployee_type->name_arabic;
    }

    return '';
}

/**
 * Get ownemployee_type ID by name (case-insensitive)
 *
 * @param string $name The ownemployee type name to search for
 * @param bool $use_arabic Whether to search in Arabic name field
 * @return int|null Returns the ID if found, null otherwise
 */
function get_ownemployee_type_id_by_name($name, $use_arabic = false)
{
    $CI = &get_instance();
    
    $field = $use_arabic ? 'name_arabic' : 'name';
    
    // Use LOWER() for case-insensitive search (for English names)
    if (!$use_arabic) {
        $CI->db->where('LOWER(' . $field . ')', strtolower($name));
    } else {
        $CI->db->where($field, $name);
    }
    
    $ownemployee_type = $CI->db->get(db_prefix() . 'ownemployeetype')->row();
    
    if ($ownemployee_type && isset($ownemployee_type->id)) {
        return (int) $ownemployee_type->id;
    }
    
    return null;
}
