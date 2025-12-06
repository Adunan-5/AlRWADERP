<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Document_types_model extends App_Model
{
    protected $table = 'tbldocument_types';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all document types
     * @param  array  $where
     * @return array
     */
    public function get($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get($this->table)->row();
        }

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Add new document type
     * @param array $data
     * @return int Insert ID
     */
    public function add($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert($this->table, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Document Type Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update document type
     * @param  int   $id   Document type ID
     * @param  array $data Update data
     * @return boolean
     */
    public function update($id, $data)
    {
        $data['modified_at'] = date('Y-m-d H:i:s');
        $data['modified_by'] = get_staff_user_id();

        $this->db->where('id', $id);
        $this->db->update($this->table, $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Document Type Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete document type
     * @param  int $id Document type ID
     * @return boolean
     */
    public function delete($id)
    {
        // Check if document type is referenced in staff_files
        $this->db->where('document_type_id', $id);
        $is_referenced = $this->db->count_all_results(db_prefix() . 'staff_files') > 0;

        if ($is_referenced) {
            return [
                'referenced' => true,
                'message' => 'This document type is being used and cannot be deleted.'
            ];
        }

        // Check if it's a system document type
        $doc_type = $this->get($id);
        if ($doc_type && $doc_type->is_system == 1) {
            return [
                'system' => true,
                'message' => 'System document types cannot be deleted.'
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete($this->table);

        if ($this->db->affected_rows() > 0) {
            log_activity('Document Type Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Check if document type name already exists
     * @param  string  $name
     * @param  int     $exclude_id
     * @return boolean
     */
    public function check_duplicate($name, $exclude_id = null)
    {
        $this->db->where('name', $name);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Get document type usage count
     * @param  int $id
     * @return int
     */
    public function get_usage_count($id)
    {
        $this->db->where('document_type_id', $id);
        return $this->db->count_all_results(db_prefix() . 'staff_files');
    }
}
