<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff_pay_model extends App_Model
{
    protected $table = 'tblstaffpay';

    public function add($data)
    {
        // Calculate GOSI fields from basic_pay and accommodation
        $basic_pay = isset($data['basic_pay']) ? floatval($data['basic_pay']) : 0;
        $accomodation = isset($data['accomodation_allowance']) ? floatval($data['accomodation_allowance']) : 0;

        $insert = [
            'staff_id'              => isset($data['staff_id']) ? $data['staff_id'] : null,
            'start_date'            => isset($data['start_date']) ? to_sql_date($data['start_date']) : date('Y-m-d'),
            'payout_type'           => isset($data['payout_type']) ? $data['payout_type'] : 'monthly',
            'basic_pay'             => $basic_pay,
            'overtime_pay'          => isset($data['overtime_pay']) ? floatval($data['overtime_pay']) : 0,
            'food_allowance'        => isset($data['food_allowance']) ? floatval($data['food_allowance']) : 0,
            'allowance'             => isset($data['allowance']) ? floatval($data['allowance']) : 0,
            'fat_allowance'         => isset($data['fat_allowance']) ? floatval($data['fat_allowance']) : 0,
            'accomodation_allowance'=> $accomodation,
            'mewa'                  => isset($data['mewa']) ? floatval($data['mewa']) : 0,
            'gosi_basic'            => isset($data['gosi_basic']) ? floatval($data['gosi_basic']) : $basic_pay,
            'gosi_housing_allowance'=> isset($data['gosi_housing_allowance']) ? floatval($data['gosi_housing_allowance']) : $accomodation,
        ];

        $this->db->insert($this->table, $insert);
        $pay_id = $this->db->insert_id();

        if (!$pay_id) {
            log_activity('Staff Pay Add Failed: ' . $this->db->error()['message']);
            return false;
        }

        // Save custom allowances
        if ($pay_id && isset($data['custom_allowances']) && is_array($data['custom_allowances'])) {
            $this->save_custom_allowances($pay_id, $data['custom_allowances']);
        }

        return $pay_id;
    }

    public function get($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    /**
     * Get pay record with custom allowances
     * @param int $id
     * @return object|null
     */
    public function get_with_allowances($id)
    {
        $pay = $this->get($id);

        if ($pay) {
            // Get custom allowances
            $custom_allowances = $this->db->where('staff_pay_id', $id)
                                          ->get(db_prefix() . 'staff_pay_allowances')
                                          ->result_array();

            $pay->custom_allowances = [];
            foreach ($custom_allowances as $allowance) {
                $pay->custom_allowances[$allowance['allowance_type_id']] = $allowance['amount'];
            }
        }

        return $pay;
    }

    /**
     * Save custom allowances for a pay record
     * @param int $pay_id
     * @param array $allowances Array with allowance_type_id as key and amount as value
     * @return bool
     */
    public function save_custom_allowances($pay_id, $allowances)
    {
        // Delete existing custom allowances
        $this->delete_custom_allowances($pay_id);

        // Insert new custom allowances
        foreach ($allowances as $allowance_id => $amount) {
            if (!empty($amount) && $amount > 0) {
                $this->db->insert(db_prefix() . 'staff_pay_allowances', [
                    'staff_pay_id'      => $pay_id,
                    'allowance_type_id' => $allowance_id,
                    'amount'            => $amount
                ]);
            }
        }

        return true;
    }

    /**
     * Delete custom allowances for a pay record
     * @param int $pay_id
     * @return bool
     */
    public function delete_custom_allowances($pay_id)
    {
        $this->db->where('staff_pay_id', $pay_id);
        return $this->db->delete(db_prefix() . 'staff_pay_allowances');
    }

    public function update($id, $data)
    {
        // Calculate GOSI fields from basic_pay and accommodation
        $basic_pay = isset($data['basic_pay']) ? floatval($data['basic_pay']) : 0;
        $accomodation = isset($data['accomodation_allowance']) ? floatval($data['accomodation_allowance']) : 0;

        $update = [
            'start_date'            => isset($data['start_date']) ? to_sql_date($data['start_date']) : date('Y-m-d'),
            'payout_type'           => isset($data['payout_type']) ? $data['payout_type'] : 'monthly',
            'basic_pay'             => $basic_pay,
            'overtime_pay'          => isset($data['overtime_pay']) ? floatval($data['overtime_pay']) : 0,
            'food_allowance'        => isset($data['food_allowance']) ? floatval($data['food_allowance']) : 0,
            'allowance'             => isset($data['allowance']) ? floatval($data['allowance']) : 0,
            'fat_allowance'         => isset($data['fat_allowance']) ? floatval($data['fat_allowance']) : 0,
            'accomodation_allowance'=> $accomodation,
            'mewa'                  => isset($data['mewa']) ? floatval($data['mewa']) : 0,
            'gosi_basic'            => isset($data['gosi_basic']) ? floatval($data['gosi_basic']) : $basic_pay,
            'gosi_housing_allowance'=> isset($data['gosi_housing_allowance']) ? floatval($data['gosi_housing_allowance']) : $accomodation,
        ];

        $this->db->where('id', $id);
        $success = $this->db->update($this->table, $update);

        if (!$success) {
            log_activity('Staff Pay Update Failed for ID ' . $id . ': ' . $this->db->error()['message']);
        }

        // Update custom allowances (even if there are none or they're empty)
        if ($success && isset($data['custom_allowances']) && is_array($data['custom_allowances'])) {
            $this->save_custom_allowances($id, $data['custom_allowances']);
        }

        return $success;
    }
}
