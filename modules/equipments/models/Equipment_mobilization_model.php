<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Equipment Mobilization model
 * Manages equipment deployments to client sites
 */
class Equipment_mobilization_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get mobilizations
     * @param  mixed $id    optional mobilization id
     * @param  array $where additional where conditions
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('m.*,
            e.equipment_number,
            e.description as equipment_description,
            e.category as equipment_category,
            c.company as client_name,
            p.name as project_name,
            o.name as operator_name');
        $this->db->from(db_prefix() . 'equipment_mobilization m');
        $this->db->join(db_prefix() . 'equipments e', 'e.id = m.equipment_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = m.client_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = m.project_id', 'left');
        $this->db->join(db_prefix() . 'operators o', 'o.id = m.operator_id', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        if (is_numeric($id)) {
            $this->db->where('m.id', $id);
            return $this->db->get()->row();
        }

        $this->db->order_by('m.mobilization_date', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Add new mobilization
     * @param array $data mobilization data
     * @return int insert ID
     */
    public function add($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $data['status'] = 'mobilized';

        $data = hooks()->apply_filters('before_mobilization_added', $data);

        $this->db->insert(db_prefix() . 'equipment_mobilization', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Update equipment status to 'deployed'
            if (isset($data['equipment_id'])) {
                $this->db->where('id', $data['equipment_id']);
                $this->db->update(db_prefix() . 'equipments', [
                    'status' => 'deployed'
                ]);
            }

            hooks()->do_action('mobilization_created', $insert_id);
            log_activity('Equipment Mobilized [Equipment ID: ' . $data['equipment_id'] . ' to Client: ' . $data['client_id'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update mobilization
     * @param  array $data mobilization data
     * @param  int   $id   mobilization id
     * @return bool
     */
    public function update($data, $id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();

        $data = hooks()->apply_filters('before_mobilization_updated', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_mobilization', $data);

        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('mobilization_updated', $id);
            log_activity('Equipment Mobilization Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Demobilize equipment (return from client site)
     * @param  int    $id                 mobilization id
     * @param  string $demobilization_date
     * @param  string $remarks
     * @return bool
     */
    public function demobilize($id, $demobilization_date = null, $remarks = '')
    {
        $mobilization = $this->get($id);

        if (!$mobilization || $mobilization->status == 'demobilized') {
            return false;
        }

        $update_data = [
            'status' => 'demobilized',
            'demobilization_date' => $demobilization_date ?: date('Y-m-d'),
            'demobilization_remarks' => $remarks,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id()
        ];

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_mobilization', $update_data);

        if ($this->db->affected_rows() > 0) {
            // Update equipment status back to 'available'
            $this->db->where('id', $mobilization->equipment_id);
            $this->db->update(db_prefix() . 'equipments', [
                'status' => 'available'
            ]);

            log_activity('Equipment Demobilized [Equipment: ' . $mobilization->equipment_number . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete mobilization
     * @param  int $id mobilization id
     * @return bool
     */
    public function delete($id)
    {
        $mobilization = $this->get($id);

        if (!$mobilization) {
            return false;
        }

        // Check if there are timesheets linked to this mobilization
        $this->db->where('mobilization_id', $id);
        $timesheet_count = $this->db->count_all_results(db_prefix() . 'equipment_timesheet');

        if ($timesheet_count > 0) {
            return [
                'success' => false,
                'message' => _l('cannot_delete_mobilization_with_timesheets')
            ];
        }

        hooks()->do_action('before_delete_mobilization', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'equipment_mobilization');

        if ($this->db->affected_rows() > 0) {
            // Update equipment status if it was mobilized
            if ($mobilization->status == 'mobilized') {
                $this->db->where('id', $mobilization->equipment_id);
                $this->db->update(db_prefix() . 'equipments', [
                    'status' => 'available'
                ]);
            }

            hooks()->do_action('mobilization_deleted', $id);
            log_activity('Equipment Mobilization Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Get active mobilizations for equipment
     * @param  int $equipment_id
     * @return array
     */
    public function get_active_by_equipment($equipment_id)
    {
        return $this->get('', [
            'm.equipment_id' => $equipment_id,
            'm.status' => 'mobilized'
        ]);
    }

    /**
     * Get mobilizations by client
     * @param  int $client_id
     * @return array
     */
    public function get_by_client($client_id)
    {
        return $this->get('', ['m.client_id' => $client_id]);
    }

    /**
     * Get mobilizations by project
     * @param  int $project_id
     * @return array
     */
    public function get_by_project($project_id)
    {
        return $this->get('', ['m.project_id' => $project_id]);
    }

    /**
     * Get mobilizations by status
     * @param  string $status (mobilized/demobilized)
     * @return array
     */
    public function get_by_status($status)
    {
        return $this->get('', ['m.status' => $status]);
    }

    /**
     * Get mobilizations by operator
     * @param  int $operator_id
     * @return array
     */
    public function get_by_operator($operator_id)
    {
        return $this->get('', ['m.operator_id' => $operator_id]);
    }

    /**
     * Get mobilization statistics
     * @return array
     */
    public function get_statistics()
    {
        $stats = [];

        // Currently mobilized equipment
        $this->db->where('status', 'mobilized');
        $stats['currently_mobilized'] = $this->db->count_all_results(db_prefix() . 'equipment_mobilization');

        // Total demobilized
        $this->db->where('status', 'demobilized');
        $stats['total_demobilized'] = $this->db->count_all_results(db_prefix() . 'equipment_mobilization');

        // By rate type
        $this->db->select('rate_type, COUNT(*) as count');
        $this->db->where('status', 'mobilized');
        $this->db->group_by('rate_type');
        $rate_types = $this->db->get(db_prefix() . 'equipment_mobilization')->result_array();

        $stats['by_rate_type'] = [];
        foreach ($rate_types as $rt) {
            $stats['by_rate_type'][$rt['rate_type']] = $rt['count'];
        }

        // Total revenue potential (active mobilizations)
        $this->db->select('
            SUM(CASE
                WHEN rate_type = "hourly" THEN rate_amount * 8 * 30
                WHEN rate_type = "daily" THEN rate_amount * 30
                WHEN rate_type = "monthly" THEN rate_amount
                ELSE 0
            END) as potential_monthly_revenue
        ');
        $this->db->where('status', 'mobilized');
        $result = $this->db->get(db_prefix() . 'equipment_mobilization')->row();
        $stats['potential_monthly_revenue'] = $result ? $result->potential_monthly_revenue : 0;

        return $stats;
    }

    /**
     * Change operator for mobilization
     * @param  int $mobilization_id
     * @param  int $new_operator_id
     * @param  string $change_date
     * @return bool
     */
    public function change_operator($mobilization_id, $new_operator_id, $change_date = null)
    {
        $mobilization = $this->get($mobilization_id);

        if (!$mobilization || $mobilization->status != 'mobilized') {
            return false;
        }

        $this->db->where('id', $mobilization_id);
        $this->db->update(db_prefix() . 'equipment_mobilization', [
            'operator_id' => $new_operator_id,
            'operator_change_date' => $change_date ?: date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id()
        ]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Operator Changed for Mobilization [ID: ' . $mobilization_id . ']');
            return true;
        }

        return false;
    }

    /**
     * Get equipment available for mobilization
     * @return array
     */
    public function get_available_equipment()
    {
        $this->db->select('e.*');
        $this->db->from(db_prefix() . 'equipments e');
        $this->db->where('e.status', 'available');
        $this->db->order_by('e.equipment_number', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get operators available for assignment
     * @param  string $operator_type optional filter by type
     * @return array
     */
    public function get_available_operators($operator_type = null)
    {
        $this->db->select('o.*');
        $this->db->from(db_prefix() . 'operators o');
        $this->db->where('o.is_active', 1);

        if ($operator_type) {
            $this->db->where('o.operator_type', $operator_type);
        }

        $this->db->order_by('o.name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Extend mobilization period
     * @param  int    $mobilization_id
     * @param  string $new_expected_end_date
     * @param  string $remarks
     * @return bool
     */
    public function extend_period($mobilization_id, $new_expected_end_date, $remarks = '')
    {
        $mobilization = $this->get($mobilization_id);

        if (!$mobilization || $mobilization->status != 'mobilized') {
            return false;
        }

        $this->db->where('id', $mobilization_id);
        $this->db->update(db_prefix() . 'equipment_mobilization', [
            'expected_demobilization_date' => $new_expected_end_date,
            'extension_remarks' => $remarks,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id()
        ]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Mobilization Period Extended [ID: ' . $mobilization_id . ' to ' . $new_expected_end_date . ']');
            return true;
        }

        return false;
    }

    /**
     * Get mobilizations ending soon
     * @param  int $days_ahead
     * @return array
     */
    public function get_ending_soon($days_ahead = 7)
    {
        $future_date = date('Y-m-d', strtotime('+' . $days_ahead . ' days'));
        $today = date('Y-m-d');

        $this->db->where('status', 'mobilized');
        $this->db->where('expected_demobilization_date >=', $today);
        $this->db->where('expected_demobilization_date <=', $future_date);

        return $this->get();
    }
}
