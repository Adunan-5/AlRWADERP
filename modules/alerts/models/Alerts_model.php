<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alerts_model extends App_Model
{
    private $expiry_columns = [
        'iqama'     => 'iqama_expiry',
        'ajeer'     => 'ajeer_expiry',
        'passport'  => 'passport_expiry',
        'insurance' => 'insurance_expiry',
        'health'    => 'health_card_expiry',
        'visa'      => 'visa_expiry',
        'atm'       => 'atm_expiry',
        'contract'  => 'contract_end_date'
    ];

    public function get_alert_counts($months = 1)
    {
        $counts = [];
        $date_limit = date('Y-m-d', strtotime("+{$months} month"));

        foreach ($this->expiry_columns as $key => $col) {
            $this->db->from('tblstaff');
            $this->db->where("$col <=", $date_limit);
            $this->db->where("$col IS NOT NULL", null, false);
            $this->db->where_not_in('staffid', [1, 3]); // 👈 exclude staffid 1 and 3
            $counts[$key] = $this->db->count_all_results();
}

        // vacation
        $this->db->from('tblstaffvacations');
        $this->db->where('expected_end_date IS NOT NULL', null, false);
        $this->db->where('expected_end_date <=', $date_limit);
        $counts['vacation'] = $this->db->count_all_results();

        return $counts;
    }

    public function get_alert_details($type, $months = 1)
    {
        $date_limit = date('Y-m-d', strtotime("+{$months} month"));

        if ($type == 'vacation') {
            $this->db->select('tblstaffvacations.*, tblstaff.name, tblstaff.iqama_number, tblstaff.staffid');
            $this->db->from('tblstaffvacations');
            $this->db->join('tblstaff', 'tblstaff.staffid = tblstaffvacations.staff_id');
            $this->db->where('expected_end_date IS NOT NULL', null, false);
            $this->db->where('expected_end_date <=', $date_limit);
            $this->db->where_not_in('tblstaff.staffid', [1, 3]); // 👈 exclude staffid 1 and 3
            return $this->db->get()->result_array();
        }

        if (!isset($this->expiry_columns[$type])) {
            return [];
        }

        $col = $this->expiry_columns[$type];
        $this->db->select("staffid, name, iqama_number, $col as expiry_date");
        $this->db->from('tblstaff');
        $this->db->where("$col <=", $date_limit);
        $this->db->where("$col IS NOT NULL", null, false);
        $this->db->where_not_in('staffid', [1, 3]); // 👈 exclude staffid 1 and 3
        return $this->db->get()->result_array();
    }
}
