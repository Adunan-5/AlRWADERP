<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all company_types stored in database
 *
 * @return array
 */
// function get_all_company_types()
// {
//     $company_types = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'companytype')->result_array();

//     return hooks()->apply_filters('all_company_types', $company_types);
// }

if (!function_exists('get_all_company_types')) {
    function get_all_company_types()
    {
        $CI = &get_instance();
        $CI->db->order_by('name', 'asc');
        return $CI->db->get(db_prefix() . 'companytype')->result_array();
    }
}

/**
 * Get a single company_type row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_company_type($id)
{
    $CI = &get_instance();

    $company_type = $CI->app_object_cache->get('db-companytype-' . $id);

    if (!$company_type) {
        $CI->db->where('id', $id);
        $company_type = $CI->db->get(db_prefix() . 'companytype')->row();
        $CI->app_object_cache->add('db-companytype-' . $id, $company_type);
    }

    return hooks()->apply_filters('get_companytype', $company_type);
}

/**
 * Get company_type name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_company_type_name($id)
{
    $company_type = get_company_type($id);
    if ($company_type) {
        return $company_type->name;
    }

    return '';
}

/**
 * Get company_type name (Arabic) by ID
 *
 * @param mixed $id
 * @return string
 */
function get_company_type_name_arabic($id)
{
    $company_type = get_company_type($id);
    if ($company_type) {
        return $company_type->name_arabic;
    }

    return '';
}

/**
 * Get company_type ID by name (case-insensitive)
 *
 * @param string $name The company type name to search for
 * @param bool $use_arabic Whether to search in Arabic name field
 * @return int|null Returns the ID if found, null otherwise
 */
function get_company_type_id_by_name($name, $use_arabic = false)
{
    $CI = &get_instance();
    
    $field = $use_arabic ? 'name_arabic' : 'name';
    
    // Use LOWER() for case-insensitive search (for English names)
    if (!$use_arabic) {
        $CI->db->where('LOWER(' . $field . ')', strtolower($name));
    } else {
        $CI->db->where($field, $name);
    }
    
    $company_type = $CI->db->get(db_prefix() . 'companytype')->row();
    
    if ($company_type && isset($company_type->id)) {
        return (int) $company_type->id;
    }
    
    return null;
}

/**
 * Get staff count for each company type
 *
 * @return array
 * [
 *   ['id' => 1, 'name' => 'Contracting', 'name_arabic' => 'مقاولات', 'staff_count' => 25],
 *   ['id' => 2, 'name' => 'Trading', 'name_arabic' => 'تجارة', 'staff_count' => 10],
 *   ...
 * ]
 */
if (!function_exists('get_company_types_with_staff_count')) {
    function get_company_types_with_staff_count()
    {
        $CI = &get_instance();

        // 1) Fetch counts per company type
        $CI->db->select('ct.name, COUNT(s.staffid) AS staff_count', false);
        $CI->db->from(db_prefix() . 'companytype AS ct');
        $CI->db->join(db_prefix() . 'staff AS s', 's.companytype_id = ct.id', 'left');
        $CI->db->group_by('ct.id, ct.name');
        $results = $CI->db->get()->result_array();

        // 2) Initialize buckets
        $final = [
            'MOHTARIFEEN' => 0,
            'MAHIROON'    => 0,
            'OTHERS'      => 0,
        ];

        // 3) Distribute results
        foreach ($results as $row) {
            $name = strtoupper(trim($row['name']));
            if ($name === 'MOHTARIFEEN') {
                $final['MOHTARIFEEN'] += (int)$row['staff_count'];
            } elseif ($name === 'MAHIROON') {
                $final['MAHIROON'] += (int)$row['staff_count'];
            } else {
                $final['OTHERS'] += (int)$row['staff_count'];
            }
        }

        // 4) Return in array format compatible with your view
        return [
            ['name' => 'MOHTARIFEEN', 'staff_count' => $final['MOHTARIFEEN']],
            ['name' => 'MAHIROON',    'staff_count' => $final['MAHIROON']],
            ['name' => 'OTHERS',      'staff_count' => $final['OTHERS']],
        ];
    }
}


/**
 * Get staff count by specific company type ID
 *
 * @param int $companytype_id
 * @return int
 */
if (!function_exists('get_staff_count_by_companytype')) {
    function get_staff_count_by_companytype($companytype_id)
    {
        $CI = &get_instance();

        $CI->db->where('companytype_id', $companytype_id);
        return (int) $CI->db->count_all_results(db_prefix() . 'staff');
    }
}
