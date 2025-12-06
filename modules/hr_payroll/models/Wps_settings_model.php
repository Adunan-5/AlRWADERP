<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Wps_settings_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();

        // Ensure table exists
        $this->create_table_if_not_exists();
    }

    /**
     * Create WPS settings table if it doesn't exist
     */
    private function create_table_if_not_exists()
    {
        if (!$this->db->table_exists(db_prefix() . 'hrp_wps_settings')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'hrp_wps_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `company_id` int(11) DEFAULT NULL COMMENT "Company ID (companytype_id)",
                `type` varchar(50) DEFAULT NULL COMMENT "WPS Type",
                `customer_name` varchar(255) DEFAULT NULL COMMENT "Customer/Company Name",
                `agreement_code` varchar(100) DEFAULT NULL COMMENT "Agreement Code",
                `funding_account` varchar(100) DEFAULT NULL COMMENT "Funding Account Number",
                `branch_no` varchar(50) DEFAULT NULL COMMENT "Branch Number",
                `credit_date_format` varchar(20) DEFAULT "DDMMYYYY" COMMENT "Credit Date Format",
                `mins_lab_establish_id` varchar(100) DEFAULT NULL COMMENT "Ministry of Labor Establishment ID",
                `ecr_id` varchar(100) DEFAULT NULL COMMENT "ECR ID",
                `bank_code` varchar(50) DEFAULT NULL COMMENT "Bank Code (e.g., RIBL)",
                `currency` varchar(10) DEFAULT "SAR" COMMENT "Currency Code",
                `batch` varchar(50) DEFAULT NULL COMMENT "Batch Number/Code",
                `file_reference` varchar(100) DEFAULT NULL COMMENT "File Reference",
                `payment_desc` varchar(255) DEFAULT NULL COMMENT "Default Payment Description",
                `payment_ref` varchar(100) DEFAULT NULL COMMENT "Default Payment Reference",
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `company_id` (`company_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

            // Insert default settings
            $this->db->insert(db_prefix() . 'hrp_wps_settings', [
                'type' => '111',
                'bank_code' => 'RIBL',
                'currency' => 'SAR',
                'credit_date_format' => 'DDMMYYYY',
                'payment_desc' => 'Salary for October 2025'
            ]);

            log_activity('WPS Settings table created');
        }
    }

    /**
     * Get WPS settings by company ID
     * @param int $company_id Company ID (null for default/all companies)
     * @return object|null
     */
    public function get($company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        } else {
            $this->db->where('company_id IS NULL');
        }

        $result = $this->db->get(db_prefix() . 'hrp_wps_settings')->row();

        // If no specific settings found, get default (company_id = null)
        if (!$result && $company_id) {
            $this->db->where('company_id IS NULL');
            $result = $this->db->get(db_prefix() . 'hrp_wps_settings')->row();
        }

        return $result;
    }

    /**
     * Get all WPS settings
     * @return array
     */
    public function get_all()
    {
        return $this->db->get(db_prefix() . 'hrp_wps_settings')->result_array();
    }

    /**
     * Add or update WPS settings
     * @param array $data Settings data
     * @param int $company_id Company ID
     * @return bool
     */
    public function save($data, $company_id = null)
    {
        // Check if record exists for this EXACT company_id (not fallback)
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        } else {
            $this->db->where('company_id IS NULL');
        }
        $existing = $this->db->get(db_prefix() . 'hrp_wps_settings')->row();

        if ($existing) {
            // Update existing record for this company
            $this->db->where('id', $existing->id);
            $this->db->update(db_prefix() . 'hrp_wps_settings', $data);

            if ($this->db->affected_rows() >= 0) {
                log_activity('WPS Settings Updated [Company ID: ' . ($company_id ?? 'Default') . ']');
                return true;
            }
        } else {
            // Insert new record for this company
            $data['company_id'] = $company_id;
            $this->db->insert(db_prefix() . 'hrp_wps_settings', $data);

            if ($this->db->insert_id()) {
                log_activity('WPS Settings Created [Company ID: ' . ($company_id ?? 'Default') . ']');
                return true;
            }
        }

        return false;
    }

    /**
     * Delete WPS settings
     * @param int $id Settings ID
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'hrp_wps_settings');

        if ($this->db->affected_rows() > 0) {
            log_activity('WPS Settings Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }
}
