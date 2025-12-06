<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Recreate equipment_agreements table with proper structure
 * Version: 1.1.0
 * Description: Drop old simple structure from v108 and create new structure with support for:
 *              - Multiple equipment per agreement (via agreement_items)
 *              - Tiered pricing (via pricing_tiers)
 *              - One-time charges (via charges)
 */
class Migration_Version_117 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Drop the old equipment_agreements table structure from migration 108
        if ($CI->db->table_exists(db_prefix() . 'equipment_agreements')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_agreements`');
        }

        // Create new equipment_agreements table with proper structure
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

    public function down()
    {
        $CI = &get_instance();

        // Drop dependent tables first to avoid foreign key constraint errors
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_agreement_pricing_tiers`');
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_agreement_charges`');
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_agreement_items`');

        // Revert to old structure (from migration 108)
        if ($CI->db->table_exists(db_prefix() . 'equipment_agreements')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_agreements`');
        }

        // Recreate old structure
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
