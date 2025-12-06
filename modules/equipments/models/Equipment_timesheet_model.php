<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Equipment Timesheet model
 * Manages equipment timesheets for client billing
 */
class Equipment_timesheet_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get timesheets
     * @param  mixed $id    optional timesheet id
     * @param  array $where additional where conditions
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('ts.*,
            e.equipment_number,
            e.description as equipment_desc,
            c.company as client_name,
            p.name as project_name,
            m.location as mobilization_location,
            s1.firstname as prepared_by_name,
            s2.firstname as verified_by_name,
            s3.firstname as approved_by_name');
        $this->db->from(db_prefix() . 'equipment_timesheet ts');
        $this->db->join(db_prefix() . 'equipments e', 'e.id = ts.equipment_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = ts.client_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = ts.project_id', 'left');
        $this->db->join(db_prefix() . 'equipment_mobilization m', 'm.id = ts.mobilization_id', 'left');
        $this->db->join(db_prefix() . 'staff s1', 's1.staffid = ts.prepared_by', 'left');
        $this->db->join(db_prefix() . 'staff s2', 's2.staffid = ts.verified_by', 'left');
        $this->db->join(db_prefix() . 'staff s3', 's3.staffid = ts.approved_by', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        if (is_numeric($id)) {
            $this->db->where('ts.id', $id);
            return $this->db->get()->row();
        }

        $this->db->order_by('ts.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Add new timesheet
     * @param array $data timesheet data
     * @return int insert ID
     */
    public function add($data)
    {
        // Extract details if provided
        $details = [];
        if (isset($data['details'])) {
            $details = $data['details'];
            unset($data['details']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $data['prepared_by'] = get_staff_user_id();
        $data['status'] = 'draft';

        // Calculate totals
        $data = $this->calculate_timesheet_totals($data, $details);

        $data = hooks()->apply_filters('before_timesheet_added', $data);

        $this->db->insert(db_prefix() . 'equipment_timesheet', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Insert daily details
            if (!empty($details)) {
                $this->save_timesheet_details($insert_id, $details);
            }

            hooks()->do_action('timesheet_created', $insert_id);
            log_activity('New Equipment Timesheet Added [' . $data['timesheet_number'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update timesheet
     * @param  array $data timesheet data
     * @param  int   $id   timesheet id
     * @return bool
     */
    public function update($data, $id)
    {
        // Extract details if provided
        $details = [];
        if (isset($data['details'])) {
            $details = $data['details'];
            unset($data['details']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();

        // Recalculate totals
        if (!empty($details)) {
            $data = $this->calculate_timesheet_totals($data, $details);
        }

        $data = hooks()->apply_filters('before_timesheet_updated', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_timesheet', $data);

        if ($this->db->affected_rows() > 0 || !empty($details)) {
            // Update daily details
            if (!empty($details)) {
                $this->save_timesheet_details($id, $details);
            }

            hooks()->do_action('timesheet_updated', $id);
            log_activity('Equipment Timesheet Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete timesheet
     * @param  int $id timesheet id
     * @return bool
     */
    public function delete($id)
    {
        $timesheet = $this->get($id);

        if (!$timesheet) {
            return false;
        }

        // Only allow deletion if status is draft
        if ($timesheet->status != 'draft') {
            return [
                'success' => false,
                'message' => _l('cannot_delete_submitted_timesheet')
            ];
        }

        hooks()->do_action('before_delete_timesheet', $id);

        // Delete details first
        $this->db->where('timesheet_id', $id);
        $this->db->delete(db_prefix() . 'equipment_timesheet_details');

        // Delete the timesheet
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'equipment_timesheet');

        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('timesheet_deleted', $id);
            log_activity('Equipment Timesheet Deleted [' . $timesheet->timesheet_number . ']');
            return true;
        }

        return false;
    }

    /**
     * Save timesheet daily details
     * @param  int   $timesheet_id
     * @param  array $details
     * @return bool
     */
    private function save_timesheet_details($timesheet_id, $details)
    {
        // Delete existing details
        $this->db->where('timesheet_id', $timesheet_id);
        $this->db->delete(db_prefix() . 'equipment_timesheet_details');

        // Insert new details
        foreach ($details as $day => $hours) {
            if ($hours > 0) {
                $this->db->insert(db_prefix() . 'equipment_timesheet_details', [
                    'timesheet_id' => $timesheet_id,
                    'day_of_month' => $day,
                    'actual_hours' => $hours,
                    'remarks' => isset($details['remarks_' . $day]) ? $details['remarks_' . $day] : ''
                ]);
            }
        }

        return true;
    }

    /**
     * Calculate timesheet totals
     * @param  array $data    master data
     * @param  array $details daily details
     * @return array updated data
     */
    private function calculate_timesheet_totals($data, $details)
    {
        // Calculate total hours from details
        $total_hours = 0;
        foreach ($details as $day => $hours) {
            if (is_numeric($hours)) {
                $total_hours += floatval($hours);
            }
        }

        $data['total_hours'] = $total_hours;

        // Calculate gross amount
        $rate_per_hour = isset($data['rate_per_hour']) ? floatval($data['rate_per_hour']) : 0;
        $gross_amount = $total_hours * $rate_per_hour;
        $data['gross_amount'] = $gross_amount;

        // Calculate payable amount
        $deduction_amount = isset($data['deduction_amount']) ? floatval($data['deduction_amount']) : 0;
        $data['payable_amount'] = $gross_amount - $deduction_amount;

        return $data;
    }

    /**
     * Get timesheet details (daily hours)
     * @param  int $timesheet_id
     * @return array
     */
    public function get_details($timesheet_id)
    {
        $this->db->where('timesheet_id', $timesheet_id);
        $this->db->order_by('day_of_month', 'ASC');
        return $this->db->get(db_prefix() . 'equipment_timesheet_details')->result_array();
    }

    /**
     * Submit timesheet for approval
     * @param  int $id timesheet id
     * @return bool
     */
    public function submit($id)
    {
        $timesheet = $this->get($id);

        if (!$timesheet || $timesheet->status != 'draft') {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_timesheet', [
            'status' => 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id()
        ]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Equipment Timesheet Submitted [' . $timesheet->timesheet_number . ']');
            // TODO: Send notification to verifier
            return true;
        }

        return false;
    }

    /**
     * Verify timesheet (Marketing Manager)
     * @param  int    $id       timesheet id
     * @param  string $action   approve/reject
     * @param  string $remarks
     * @return bool
     */
    public function verify($id, $action = 'approve', $remarks = '')
    {
        $timesheet = $this->get($id);

        if (!$timesheet || $timesheet->status != 'submitted') {
            return false;
        }

        $update_data = [
            'verified_by' => get_staff_user_id(),
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_remarks' => $remarks,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id()
        ];

        if ($action == 'approve') {
            $update_data['status'] = 'verified';
        } else {
            $update_data['status'] = 'rejected';
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_timesheet', $update_data);

        if ($this->db->affected_rows() > 0) {
            $status_text = $action == 'approve' ? 'Verified' : 'Rejected';
            log_activity('Equipment Timesheet ' . $status_text . ' [' . $timesheet->timesheet_number . ']');
            // TODO: Send notification
            return true;
        }

        return false;
    }

    /**
     * Approve timesheet (General Manager)
     * @param  int    $id       timesheet id
     * @param  string $action   approve/reject
     * @param  string $remarks
     * @return bool
     */
    public function approve($id, $action = 'approve', $remarks = '')
    {
        $timesheet = $this->get($id);

        if (!$timesheet || $timesheet->status != 'verified') {
            return false;
        }

        $update_data = [
            'approved_by' => get_staff_user_id(),
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_remarks' => $remarks,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id()
        ];

        if ($action == 'approve') {
            $update_data['status'] = 'approved';
        } else {
            $update_data['status'] = 'rejected';
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'equipment_timesheet', $update_data);

        if ($this->db->affected_rows() > 0) {
            $status_text = $action == 'approve' ? 'Approved' : 'Rejected';
            log_activity('Equipment Timesheet ' . $status_text . ' [' . $timesheet->timesheet_number . ']');
            // TODO: Send notification
            return true;
        }

        return false;
    }

    /**
     * Generate invoice from approved timesheet
     * @param  int $id timesheet id
     * @return int invoice id
     */
    public function generate_invoice($id)
    {
        $timesheet = $this->get($id);

        if (!$timesheet || $timesheet->status != 'approved') {
            return false;
        }

        $this->load->model('invoices_model');

        // Prepare invoice data
        $invoice_data = [
            'clientid' => $timesheet->client_id,
            'project_id' => $timesheet->project_id,
            'number' => $this->invoices_model->get_next_invoice_number(),
            'date' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+30 days')),
            'currency' => get_base_currency()->id,
            'subtotal' => $timesheet->payable_amount,
            'total' => $timesheet->payable_amount,
            'terms' => '',
            'clientnote' => 'Equipment Timesheet: ' . $timesheet->timesheet_number,
            'adminnote' => 'Generated from timesheet #' . $timesheet->timesheet_number,
        ];

        $invoice_id = $this->invoices_model->add($invoice_data);

        if ($invoice_id) {
            // Add invoice item
            $item_data = [
                'description' => $timesheet->equipment_description,
                'long_description' => 'Period: ' . $timesheet->period_month . "\n" .
                                    'Driver: ' . $timesheet->driver_name . "\n" .
                                    'Plate: ' . $timesheet->plate_number . "\n" .
                                    'Total Hours: ' . $timesheet->total_hours . "\n" .
                                    'Rate/Hour: ' . $timesheet->rate_per_hour,
                'qty' => 1,
                'rate' => $timesheet->payable_amount,
                'rel_id' => $invoice_id,
                'rel_type' => 'invoice',
            ];

            $this->db->insert(db_prefix() . 'itemable', $item_data);

            // Update timesheet status
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'equipment_timesheet', [
                'status' => 'invoiced',
                'invoice_id' => $invoice_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_staff_user_id()
            ]);

            log_activity('Invoice Generated from Timesheet [' . $timesheet->timesheet_number . ']');
            return $invoice_id;
        }

        return false;
    }

    /**
     * Generate next timesheet number
     * @return string
     */
    public function get_next_timesheet_number()
    {
        $prefix = get_option('equipment_timesheet_prefix') ?: 'ETS-';
        $year = date('Y');
        $month = date('m');

        $this->db->select('COUNT(*) + 1 as next_number');
        $this->db->where('YEAR(created_at)', $year);
        $this->db->where('MONTH(created_at)', $month);
        $result = $this->db->get(db_prefix() . 'equipment_timesheet')->row();

        $next_number = $result ? $result->next_number : 1;

        return $prefix . $year . $month . str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get timesheets by status
     * @param  string $status
     * @return array
     */
    public function get_by_status($status)
    {
        return $this->get('', ['ts.status' => $status]);
    }

    /**
     * Get timesheets by period
     * @param  string $period_month YYYY-MM format
     * @return array
     */
    public function get_by_period($period_month)
    {
        return $this->get('', ['ts.period_month' => $period_month]);
    }

    /**
     * Get timesheets by client
     * @param  int $client_id
     * @return array
     */
    public function get_by_client($client_id)
    {
        return $this->get('', ['ts.client_id' => $client_id]);
    }

    /**
     * Get timesheets statistics
     * @return array
     */
    public function get_statistics()
    {
        $stats = [];

        // By status
        $statuses = ['draft', 'submitted', 'verified', 'approved', 'invoiced', 'rejected'];
        foreach ($statuses as $status) {
            $this->db->where('status', $status);
            $stats[$status] = $this->db->count_all_results(db_prefix() . 'equipment_timesheet');
        }

        // Total revenue (approved + invoiced)
        $this->db->select_sum('payable_amount');
        $this->db->where_in('status', ['approved', 'invoiced']);
        $result = $this->db->get(db_prefix() . 'equipment_timesheet')->row();
        $stats['total_revenue'] = $result ? $result->payable_amount : 0;

        // Current month pending approval
        $this->db->where('period_month', date('Y-m'));
        $this->db->where_in('status', ['submitted', 'verified']);
        $stats['pending_this_month'] = $this->db->count_all_results(db_prefix() . 'equipment_timesheet');

        return $stats;
    }

    /**
     * Import timesheet from Excel
     * @param  string $file_path
     * @return array result
     */
    public function import_from_excel($file_path)
    {
        // TODO: Implement Excel import logic
        // Use PHPSpreadsheet library to read Excel file
        // Parse daily hours grid
        // Create timesheet with details

        return [
            'success' => false,
            'message' => 'Excel import feature coming soon'
        ];
    }

    /**
     * Export timesheet to Excel
     * @param  int $id timesheet id
     * @return string file path
     */
    public function export_to_excel($id)
    {
        // TODO: Implement Excel export logic
        // Use PHPSpreadsheet library
        // Format matching user's screenshot
        // Include approval signatures

        return false;
    }
}
