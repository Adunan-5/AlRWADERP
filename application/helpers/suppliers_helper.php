<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all suppliers stored in database
 *
 * @return array
 */
function get_all_suppliers()
{
    $suppliers = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'suppliers')->result_array();

    return hooks()->apply_filters('all_suppliers', $suppliers);
}

/**
 * Get a single supplier row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_supplier($id)
{
    $CI = &get_instance();

    $supplier = $CI->app_object_cache->get('db-supplier-' . $id);

    if (!$supplier) {
        $CI->db->where('id', $id);
        $supplier = $CI->db->get(db_prefix() . 'suppliers')->row();
        $CI->app_object_cache->add('db-supplier-' . $id, $supplier);
    }

    return hooks()->apply_filters('get_supplier', $supplier);
}

/**
 * Get supplier name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_supplier_name($id)
{
    $supplier = get_supplier($id);
    if ($supplier) {
        return $supplier->name;
    }

    return '';
}

/**
 * Get supplier display name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_supplier_display_name($id)
{
    $supplier = get_supplier($id);
    if ($supplier) {
        return $supplier->display_name;
    }

    return '';
}
