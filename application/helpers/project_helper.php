<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all projects stored in database
 *
 * @return array
 */
function get_all_projects()
{
    $projects = get_instance()->db->order_by('name', 'asc')->get(db_prefix() . 'projects')->result_array();

    return hooks()->apply_filters('all_projects', $projects);
}

/**
 * Get a single project row by ID
 *
 * @param mixed $id
 * @return object|null
 */
// function get_project($id)
// {
//     $CI = &get_instance();

//     $project = $CI->app_object_cache->get('db-project-' . $id);

//     if (!$project) {
//         $CI->db->where('id', $id);
//         $project = $CI->db->get(db_prefix() . 'projects')->row();
//         $CI->app_object_cache->add('db-project-' . $id, $project);
//     }

//     return hooks()->apply_filters('get_project', $project);
// }

/**
 * Get project name by ID
 *
 * @param mixed $id
 * @return string
 */
// function get_project_name($id)
// {
//     $project = get_project($id);
//     if ($project) {
//         return $project->name;
//     }

//     return '';
// }

/**
 * Get project name (Arabic) by ID
 *
 * @param mixed $id
 * @return string
 */
// function get_project_name_arabic($id)
// {
//     $project = get_project($id);
//     if ($project && isset($project->name_arabic)) {
//         return $project->name_arabic;
//     }

//     return '';
// }

/**
 * Get project ID by name (case-insensitive)
 *
 * @param string $name The project name to search for
 * @return int|null Returns the ID if found, null otherwise
 */
function get_project_id_by_name($name)
{
    $CI = &get_instance();
    
    $CI->db->where('LOWER(name)', strtolower($name));
    $project = $CI->db->get(db_prefix() . 'projects')->row();
    
    if ($project && isset($project->id)) {
        return (int) $project->id;
    }
    
    return null;
}