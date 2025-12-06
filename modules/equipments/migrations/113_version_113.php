<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment_agreements table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_agreements')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_agreements` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `agreement_number` VARCHAR(100) NOT NULL,
                `agreement_type` ENUM(\'supplier\',\'client\') NOT NULL COMMENT \'supplier=procurement, client=sales\',
                `party_id` INT(11) NOT NULL COMMENT \'supplier_id or client_id depending on type\',
                `project_id` INT(11) NULL COMMENT \'Link to tblprojects if client agreement\',
                `start_date` DATE NOT NULL,
                `end_date` DATE NULL,
                `duration_months` INT(11) NULL,
                `payment_terms_days` INT(11) DEFAULT 30 COMMENT \'Payment terms in days (30, 45, 60)\',
                `currency` VARCHAR(10) DEFAULT \'SAR\',
                `status` ENUM(\'draft\',\'active\',\'expired\',\'terminated\',\'completed\') DEFAULT \'draft\',
                `signed_date` DATE NULL,
                `contract_file` TEXT NULL COMMENT \'Path to signed contract PDF\',
                `notes` TEXT NULL,
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_agreement_number` (`agreement_number`),
                KEY `idx_agreement_type` (`agreement_type`),
                KEY `idx_party_id` (`party_id`),
                KEY `idx_project_id` (`project_id`),
                KEY `idx_status` (`status`),
                KEY `idx_start_date` (`start_date`),
                KEY `idx_end_date` (`end_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop equipment_agreements table
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_agreements`;');
    }
}
