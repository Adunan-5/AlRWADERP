<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create equipment agreements table
 * Version: 1.0.8
 * Description: Manage supplier and client agreements
 */
class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment agreements table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_agreements')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_agreements` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `agreement_number` VARCHAR(100) DEFAULT NULL,
                `agreement_type` ENUM("supplier","client") NOT NULL,
                `party_id` INT(11) NOT NULL COMMENT "Supplier ID or Client ID",
                `party_name` VARCHAR(255) DEFAULT NULL COMMENT "Cached party name",
                `equipment_id` INT(11) DEFAULT NULL COMMENT "Specific equipment or NULL for general",
                `start_date` DATE NOT NULL,
                `end_date` DATE DEFAULT NULL,
                `renewal_date` DATE DEFAULT NULL,
                `auto_renew` TINYINT(1) DEFAULT 0,
                `terms` TEXT DEFAULT NULL,
                `payment_terms` TEXT DEFAULT NULL,
                `rate_structure` TEXT DEFAULT NULL COMMENT "JSON or text description",
                `contract_value` DECIMAL(15,2) DEFAULT NULL,
                `document_path` VARCHAR(500) DEFAULT NULL,
                `status` ENUM("draft","active","expired","terminated","renewed") DEFAULT "draft",
                `reminder_sent` TINYINT(1) DEFAULT 0,
                `notes` TEXT DEFAULT NULL,
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `terminated_by` INT(11) DEFAULT NULL,
                `terminated_at` DATETIME DEFAULT NULL,
                `termination_reason` TEXT DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `agreement_number` (`agreement_number`),
                KEY `idx_agreement_type` (`agreement_type`),
                KEY `idx_party_id` (`party_id`),
                KEY `idx_equipment_id` (`equipment_id`),
                KEY `idx_status` (`status`),
                KEY `idx_end_date` (`end_date`),
                CONSTRAINT `fk_equipment_agreements_equipment`
                    FOREIGN KEY (`equipment_id`) REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_agreements')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_agreements`');
        }
    }
}
