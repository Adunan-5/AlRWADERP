<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_121 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment PO pricing tiers table
        // Example: Months 1-12 @ 55,000 SAR/month, Month 13+ @ 85,000 SAR/month
        if (!$CI->db->table_exists(db_prefix() . 'equipment_po_pricing_tiers')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_po_pricing_tiers` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `po_item_id` INT(11) NOT NULL COMMENT "FK to equipment_po_items",
                `from_month` INT(11) NOT NULL COMMENT "Starting month number (e.g., 1, 7, 13)",
                `to_month` INT(11) NULL COMMENT "Ending month number. NULL = indefinite/ongoing",
                `monthly_rate` DECIMAL(12,2) NOT NULL COMMENT "Monthly rate for this tier",
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `po_item_id` (`po_item_id`),
                KEY `from_month` (`from_month`),
                CONSTRAINT `fk_po_pricing_tiers_item` FOREIGN KEY (`po_item_id`)
                    REFERENCES `' . db_prefix() . 'equipment_po_items` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_po_pricing_tiers')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_po_pricing_tiers`');
        }
    }
}
