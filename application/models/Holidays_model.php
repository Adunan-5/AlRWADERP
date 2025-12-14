<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Holidays_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all holidays
     * @param array $where - Optional where conditions
     * @return array
     */
    public function get($id = null)
    {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'holidays')->row();
        }

        $this->db->order_by('holiday_date', 'ASC');
        return $this->db->get(db_prefix() . 'holidays')->result_array();
    }

    /**
     * Add new holiday
     * @param array $data
     * @return int - inserted ID
     */
    public function add($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix() . 'holidays', $data);
        return $this->db->insert_id();
    }

    /**
     * Update holiday
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'holidays', $data);
    }

    /**
     * Delete holiday
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(db_prefix() . 'holidays');
    }

    /**
     * Get holidays for a specific date range
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_holidays_in_range($start_date, $end_date)
    {
        $this->db->where('holiday_date >=', $start_date);
        $this->db->where('holiday_date <=', $end_date);
        $this->db->order_by('holiday_date', 'ASC');
        return $this->db->get(db_prefix() . 'holidays')->result_array();
    }

    /**
     * Get all holiday dates as array (for quick lookup)
     * @return array - Array of dates in Y-m-d format
     */
    public function get_holiday_dates()
    {
        $this->db->select('holiday_date');
        $holidays = $this->db->get(db_prefix() . 'holidays')->result_array();

        return array_column($holidays, 'holiday_date');
    }

    /**
     * Check if a holiday already exists for a specific date
     * @param string $date - Date in Y-m-d format
     * @param int $exclude_id - Optional ID to exclude (for update checks)
     * @return bool
     */
    public function date_exists($date, $exclude_id = null)
    {
        $this->db->where('holiday_date', $date);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->count_all_results(db_prefix() . 'holidays') > 0;
    }
}
