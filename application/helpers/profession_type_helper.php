<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all profession_types stored in database
 *
 * @return array
 */
function get_all_profession_types()
{
    $profession_types = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'professiontype')->result_array();

    return hooks()->apply_filters('all_profession_types', $profession_types);
}

/**
 * Get a single profession_type row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_profession_type($id)
{
    $CI = &get_instance();

    $profession_type = $CI->app_object_cache->get('db-professiontype-' . $id);

    if (!$profession_type) {
        $CI->db->where('id', $id);
        $profession_type = $CI->db->get(db_prefix() . 'professiontype')->row();
        $CI->app_object_cache->add('db-professiontype-' . $id, $profession_type);
    }

    return hooks()->apply_filters('get_professiontype', $profession_type);
}

/**
 * Get profession_type name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_profession_type_name($id)
{
    $profession_type = get_profession_type($id);
    if ($profession_type) {
        return $profession_type->name;
    }

    return '';
}

/**
 * Get profession_type name (Arabic) by ID
 *
 * @param mixed $id
 * @return string
 */
function get_profession_type_name_arabic($id)
{
    $profession_type = get_profession_type($id);
    if ($profession_type) {
        return $profession_type->name_arabic;
    }

    return '';
}

/**
 * Get profession_type ID by name (case-insensitive)
 *
 * @param string $name The profession type name to search for
 * @param bool $use_arabic Whether to search in Arabic name field
 * @return int|null Returns the ID if found, null otherwise
 */
function get_profession_type_id_by_name($name, $use_arabic = false)
{
    $CI = &get_instance();
    
    $field = $use_arabic ? 'name_arabic' : 'name';
    
    // Use LOWER() for case-insensitive search (for English names)
    if (!$use_arabic) {
        $CI->db->where('LOWER(' . $field . ')', strtolower($name));
    } else {
        $CI->db->where($field, $name);
    }
    
    $profession_type = $CI->db->get(db_prefix() . 'professiontype')->row();
    
    if ($profession_type && isset($profession_type->id)) {
        return (int) $profession_type->id;
    }
    
    return null;
}
