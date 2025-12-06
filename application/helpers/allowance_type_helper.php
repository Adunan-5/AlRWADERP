<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all allowance types stored in database
 *
 * @param bool $active_only Whether to return only active allowances
 * @return array
 */
if (!function_exists('get_all_allowance_types')) {
    function get_all_allowance_types($active_only = false)
    {
        $CI = &get_instance();

        if ($active_only) {
            $CI->db->where('is_active', 1);
        }

        $CI->db->order_by('sort_order', 'asc');
        $CI->db->order_by('name', 'asc');
        return $CI->db->get(db_prefix() . 'allowance_types')->result_array();
    }
}

/**
 * Get a single allowance type row by ID
 *
 * @param mixed $id
 * @return object|null
 */
if (!function_exists('get_allowance_type')) {
    function get_allowance_type($id)
    {
        $CI = &get_instance();

        $allowance_type = $CI->app_object_cache->get('db-allowancetype-' . $id);

        if (!$allowance_type) {
            $CI->db->where('id', $id);
            $allowance_type = $CI->db->get(db_prefix() . 'allowance_types')->row();
            $CI->app_object_cache->add('db-allowancetype-' . $id, $allowance_type);
        }

        return $allowance_type;
    }
}

/**
 * Get allowance type name by ID
 *
 * @param mixed $id
 * @return string
 */
if (!function_exists('get_allowance_type_name')) {
    function get_allowance_type_name($id)
    {
        $allowance_type = get_allowance_type($id);
        if ($allowance_type) {
            return $allowance_type->name;
        }

        return '';
    }
}

/**
 * Get allowance type name (Arabic) by ID
 *
 * @param mixed $id
 * @return string
 */
if (!function_exists('get_allowance_type_name_arabic')) {
    function get_allowance_type_name_arabic($id)
    {
        $allowance_type = get_allowance_type($id);
        if ($allowance_type && isset($allowance_type->name_arabic)) {
            return $allowance_type->name_arabic;
        }

        return '';
    }
}

/**
 * Get allowances for specific employee type
 *
 * @param string $type 'company_type' or 'profession_type'
 * @param int $type_id
 * @return array
 */
if (!function_exists('get_allowances_for_employee_type')) {
    function get_allowances_for_employee_type($type, $type_id)
    {
        $CI = &get_instance();

        $CI->db->select('at.*, aa.is_mandatory, aa.default_amount');
        $CI->db->from(db_prefix() . 'allowance_types at');
        $CI->db->join(db_prefix() . 'allowance_assignments aa', 'aa.allowance_type_id = at.id');
        $CI->db->where('aa.employee_type', $type);
        $CI->db->where('aa.employee_type_id', $type_id);
        $CI->db->where('at.is_active', 1);
        $CI->db->order_by('at.sort_order', 'asc');
        $CI->db->order_by('at.name', 'asc');

        return $CI->db->get()->result_array();
    }
}

/**
 * Get custom allowances for a staff pay record
 *
 * @param int $pay_id
 * @return array Array with allowance_type_id as key and amount as value
 */
if (!function_exists('get_staff_pay_custom_allowances')) {
    function get_staff_pay_custom_allowances($pay_id)
    {
        $CI = &get_instance();

        $CI->db->select('allowance_type_id, amount');
        $CI->db->where('staff_pay_id', $pay_id);
        $allowances = $CI->db->get(db_prefix() . 'staff_pay_allowances')->result_array();

        $result = [];
        foreach ($allowances as $allowance) {
            $result[$allowance['allowance_type_id']] = $allowance['amount'];
        }

        return $result;
    }
}

/**
 * Calculate total allowances for a staff pay record
 *
 * @param int $pay_id
 * @return float
 */
if (!function_exists('calculate_total_custom_allowances')) {
    function calculate_total_custom_allowances($pay_id)
    {
        $CI = &get_instance();

        $CI->db->select_sum('amount');
        $CI->db->where('staff_pay_id', $pay_id);
        $result = $CI->db->get(db_prefix() . 'staff_pay_allowances')->row();

        return $result ? (float)$result->amount : 0;
    }
}

/**
 * Get total pay including all allowances
 *
 * @param object $pay Pay record object
 * @return float
 */
if (!function_exists('calculate_total_pay')) {
    function calculate_total_pay($pay)
    {
        $total = 0;

        // Fixed allowances
        $total += (float)$pay->basic_pay;
        $total += (float)$pay->overtime_pay;
        $total += (float)$pay->food_allowance;
        $total += (float)$pay->allowance;
        $total += (float)$pay->fat_allowance;
        $total += (float)$pay->accomodation_allowance;
        $total += (float)$pay->mewa;

        // GOSI fields
        if (isset($pay->gosi_basic)) {
            $total += (float)$pay->gosi_basic;
        }
        if (isset($pay->gosi_housing_allowance)) {
            $total += (float)$pay->gosi_housing_allowance;
        }

        // Custom allowances
        if (isset($pay->id)) {
            $total += calculate_total_custom_allowances($pay->id);
        }

        return $total;
    }
}
