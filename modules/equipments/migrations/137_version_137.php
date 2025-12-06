<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 137: Create Missing Supplier Quotations Tables
 *
 * Issue: Migration 136 renamed CLIENT quotations tables successfully,
 * but failed to create SUPPLIER quotations tables in Step 2.
 *
 * This migration creates the missing tables for supplier quotations
 * (responses to RFQ from suppliers).
 */
class Migration_Version_137 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // Create equipment_quotations table (SUPPLIER quotations)
        if (!$CI->db->table_exists($prefix . 'equipment_quotations')) {
            $CI->db->query("CREATE TABLE `{$prefix}equipment_quotations` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `quotation_number` VARCHAR(50) NOT NULL COMMENT 'Auto-generated quotation number (e.g., QUOT-2025-001)',
                `rfq_id` INT(11) NOT NULL COMMENT 'Links to the RFQ this quotation responds to',
                `supplier_id` INT(11) NOT NULL COMMENT 'Supplier submitting this quotation',
                `quotation_date` DATE NOT NULL COMMENT 'Date quotation was submitted',
                `valid_until_date` DATE NULL COMMENT 'Expiry date of quotation',
                `status` ENUM('draft','submitted','under_review','accepted','rejected','expired') NOT NULL DEFAULT 'draft',
                `currency` VARCHAR(10) NOT NULL DEFAULT 'SAR',
                `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Sum of all item totals',
                `tax_percentage` DECIMAL(5,2) NULL DEFAULT 0.00,
                `tax_amount` DECIMAL(15,2) NULL DEFAULT 0.00,
                `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Final total including tax',
                `payment_terms` TEXT NULL COMMENT 'Payment terms offered by supplier',
                `delivery_terms` TEXT NULL COMMENT 'Delivery/mobilization terms',
                `notes` TEXT NULL COMMENT 'Internal notes or supplier remarks',
                `created_by` INT(11) NULL COMMENT 'Staff who entered this quotation',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `quotation_number` (`quotation_number`),
                KEY `rfq_id` (`rfq_id`),
                KEY `supplier_id` (`supplier_id`),
                KEY `status` (`status`),
                CONSTRAINT `fk_quotations_rfq` FOREIGN KEY (`rfq_id`)
                    REFERENCES `{$prefix}equipment_rfq` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        }

        // Create equipment_quotation_items table (SUPPLIER quotation items)
        if (!$CI->db->table_exists($prefix . 'equipment_quotation_items')) {
            $CI->db->query("CREATE TABLE `{$prefix}equipment_quotation_items` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `quotation_id` INT(11) NOT NULL,
                `rfq_item_id` INT(11) NULL COMMENT 'Links back to the RFQ item being quoted',
                `item_type` ENUM('equipment','operator','equipment_with_operator') NOT NULL DEFAULT 'equipment',
                `equipment_description` VARCHAR(500) NULL,
                `operator_description` VARCHAR(500) NULL,
                `quantity` INT(11) NOT NULL DEFAULT 1,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'unit',
                `standard_hours_per_day` DECIMAL(5,2) NULL,
                `days_per_month` INT(11) NULL,
                `duration_months` INT(11) NULL,
                `unit_rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Quoted price per unit',
                `line_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'quantity × unit_rate',
                `notes` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `quotation_id` (`quotation_id`),
                KEY `rfq_item_id` (`rfq_item_id`),
                CONSTRAINT `fk_quotation_items_quotation` FOREIGN KEY (`quotation_id`)
                    REFERENCES `{$prefix}equipment_quotations` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        }

        log_activity('Equipment Module: Migration 137 completed - Created missing supplier quotations tables');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // Drop supplier quotations tables
        if ($CI->db->table_exists($prefix . 'equipment_quotation_items')) {
            $CI->db->query("DROP TABLE `{$prefix}equipment_quotation_items`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotations')) {
            $CI->db->query("DROP TABLE `{$prefix}equipment_quotations`");
        }

        log_activity('Equipment Module: Migration 137 rolled back - Dropped supplier quotations tables');
    }
}
