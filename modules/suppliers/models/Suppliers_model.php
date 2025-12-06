<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers_model extends App_Model
{
    protected $table = 'tblsuppliers';

    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if ($id != '') {
            $this->db->where('id', $id);
            return $this->db->get($this->table)->row();
        }
        return $this->db->get($this->table)->result_array();
    }

    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    // ✅ Get employees for a supplier (with supplier name)
    public function getSupplierEmployees($supplier_id)
    {
        $this->db->select('
            s.staffid,
            s.iqama_number,
            s.name,
            pro.name AS project_name,
            s.basics,
            s.ot,
            sk.name AS skills,
            s.contract_end_date,
            sup.name AS supplier_name
        ');
        $this->db->from('tblstaff s');
        $this->db->join('tblsuppliers sup', 's.supplier_id = sup.id', 'left');
        $this->db->join('tblprofessiontype sk', 's.professiontype_id = sk.id', 'left'); // ✅ Join skills
        $this->db->join('tblprojectassignee spa', 's.staffid = spa.staff_id', 'left'); // ✅ Join skills
        $this->db->join('tblprojects pro', 'spa.project_id = pro.id', 'left'); // ✅ Join skills
        $this->db->where('s.supplier_id', $supplier_id);
        $query = $this->db->get();
        return $query->result();
    }
}
