<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_128 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Drop all incomplete PO-related tables
        if ($CI->db->table_exists(db_prefix() . 'equipment_po_pricing_tiers')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_po_pricing_tiers`');
        }

        if ($CI->db->table_exists(db_prefix() . 'equipment_po_charges')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_po_charges`');
        }

        if ($CI->db->table_exists(db_prefix() . 'equipment_po_items')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_po_items`');
        }

        // Recreate the table with correct structure
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_po_items` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `po_id` INT(11) NOT NULL COMMENT "FK to equipment_purchase_orders",
            `equipment_id` INT(11) NULL COMMENT "FK to equipments table (NULL for unlisted/new equipment)",
            `equipment_description` VARCHAR(500) NOT NULL COMMENT "Equipment description/specification",
            `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT "Number of units",
            `unit` VARCHAR(50) NOT NULL DEFAULT "unit" COMMENT "Unit of measure (unit, set, etc.)",
            `standard_hours_per_day` DECIMAL(5,2) NULL COMMENT "Standard working hours per day",
            `days_per_month` INT(11) NULL COMMENT "Expected working days per month",
            `overtime_rate_multiplier` DECIMAL(5,2) NULL DEFAULT 1.5 COMMENT "Overtime rate multiplier (e.g., 1.5x)",
            `notes` TEXT NULL COMMENT "Item-specific notes",
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `po_id` (`po_id`),
            KEY `equipment_id` (`equipment_id`),
            CONSTRAINT `fk_po_items_po` FOREIGN KEY (`po_id`)
                REFERENCES `' . db_prefix() . 'equipment_purchase_orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_po_items_equipment` FOREIGN KEY (`equipment_id`)
                REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_po_items')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_po_items`');
        }
    }
}
