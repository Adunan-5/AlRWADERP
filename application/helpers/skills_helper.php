<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all skills stored in database
 *
 * @return array
 */
// function get_all_skills()
// {
//     $skills = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'skills')->result_array();

//     return hooks()->apply_filters('all_skills', $skills);
// }

function get_all_skills()
{
 
 $CI = &get_instance();
    $CI->db->order_by('name', 'asc');
    $skills = $CI->db->get(db_prefix() . 'skills')->result_array();
 
      // Add a separator (using optgroup)
    $skills[] = ['id' => '', 'name' => '---separator---', 'is_separator' => true];
 
    // Add "Add New" option at end
    $skills[] = ['id' => 'add_new', 'name' => '➕ Add New Skill'];
 
    return hooks()->apply_filters('all_skills', $skills);
}

/**
 * Get a single skill row by ID
 *
 * @param mixed $id
 * @return object|null
 */
function get_skill($id)
{
    $CI = &get_instance();

    $skill = $CI->app_object_cache->get('db-skill-' . $id);

    if (!$skill) {
        $CI->db->where('id', $id);
        $skill = $CI->db->get(db_prefix() . 'skills')->row();
        $CI->app_object_cache->add('db-skill-' . $id, $skill);
    }

    return hooks()->apply_filters('get_skill', $skill);
}

/**
 * Get skill name by ID
 *
 * @param mixed $id
 * @return string
 */
function get_skill_name($id)
{
    $skill = get_skill($id);
    if ($skill) {
        return $skill->name;
    }

    return '';
}

/**
 * Get skill name (Arabic) by ID
 *
 * @param mixed $id
 * @return string
 */
function get_skill_name_arabic($id)
{
    $skill = get_skill($id);
    if ($skill) {
        return $skill->name_arabic;
    }

    return '';
}
