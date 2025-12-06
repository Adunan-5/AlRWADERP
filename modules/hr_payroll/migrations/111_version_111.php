<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create WPS settings table
        if (!$CI->db->table_exists(db_prefix() . 'hrp_wps_settings')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'hrp_wps_settings` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        // Insert default WPS settings
        if (!$CI->db->get_where(db_prefix() . 'hrp_wps_settings', ['id' => 1])->row()) {
            $CI->db->insert(db_prefix() . 'hrp_wps_settings', [
                'type' => '111',
                'customer_name' => '',
                'agreement_code' => '',
                'funding_account' => '',
                'branch_no' => '',
                'credit_date_format' => 'DDMMYYYY',
                'mins_lab_establish_id' => '',
                'ecr_id' => '',
                'bank_code' => 'RIBL',
                'currency' => 'SAR',
                'batch' => '',
                'file_reference' => '',
                'payment_desc' => 'Salary for October 2025',
                'payment_ref' => ''
            ]);
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'hrp_wps_settings')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'hrp_wps_settings`');
        }
    }
}
