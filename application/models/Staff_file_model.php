<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff_file_model extends App_Model
{
    protected $table = 'tblstaff_files';

    public function add($data)
    {
        $insert = [
            'staff_id'         => $data['staff_id'],
            'document_type_id' => isset($data['document_type_id']) ? $data['document_type_id'] : null,
            'document_type'    => $data['document_type'],
            'caption'          => $data['caption'],
            'file_name'        => $data['file_name'],
            'file_path'        => $data['file_path'],
        ];
        $this->db->insert($this->table, $insert);
        return $this->db->insert_id();
    }

    public function get($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function update($id, $data)
    {
        $update = [
            'document_type_id' => isset($data['document_type_id']) ? $data['document_type_id'] : null,
            'document_type'    => $data['document_type'],
            'caption'          => $data['caption'],
        ];

        if (!empty($data['file_name'])) {
            $update['file_name'] = $data['file_name'];
            $update['file_path'] = $data['file_path'];
        }

        $this->db->where('id', $id);
        return $this->db->update($this->table, $update);
    }

    public function delete($id)
    {
        $file = $this->get($id);
        if (!$file) {
            return false;
        }

        // Delete physical file
        if (!empty($file->file_path) && file_exists($file->file_path)) {
            unlink($file->file_path);
        }

        // Delete from database
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}
