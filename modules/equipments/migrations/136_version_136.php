<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 136: Resolve Table Naming Conflict
 *
 * Problem: Migration 123 and Migration 134 both use "tblequipment_quotations" table name
 * - Migration 123: CLIENT quotations (sales quotes to clients)
 * - Migration 134: SUPPLIER quotations (responses to RFQ from suppliers)
 *
 * Solution: Rename CLIENT quotations tables to "client_quotations" prefix
 * Then create SUPPLIER quotations tables with original name
 *
 * Tables renamed:
 * - tblequipment_quotations → tblequipment_client_quotations
 * - tblequipment_quotation_items → tblequipment_client_quotation_items
 * - tblequipment_quotation_pricing_tiers → tblequipment_client_quotation_pricing_tiers
 * - tblequipment_quotation_charges → tblequipment_client_quotation_charges
 *
 * Tables created (Migration 134 schema):
 * - tblequipment_quotations (supplier quotations)
 * - tblequipment_quotation_items (supplier quotation items)
 */
class Migration_Version_136 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // =====================================================================
        // STEP 1: Rename CLIENT quotations tables (Migration 123-126)
        // =====================================================================

        // Drop foreign key constraints first (must drop before renaming)
        if ($CI->db->table_exists($prefix . 'equipment_quotations')) {
            // Drop constraints from quotations table
            $this->drop_foreign_key_if_exists($prefix . 'equipment_quotations', 'fk_quotations_client');
            $this->drop_foreign_key_if_exists($prefix . 'equipment_quotations', 'fk_quotations_agreement');
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_items')) {
            // Drop constraints from quotation_items table
            $this->drop_foreign_key_if_exists($prefix . 'equipment_quotation_items', 'fk_quotation_items_quotation');
            $this->drop_foreign_key_if_exists($prefix . 'equipment_quotation_items', 'fk_quotation_items_equipment');
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_pricing_tiers')) {
            // Drop constraints from pricing_tiers table
            $this->drop_foreign_key_if_exists($prefix . 'equipment_quotation_pricing_tiers', 'fk_quotation_pricing_tier_item');
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_charges')) {
            // Drop constraints from charges table
            $this->drop_foreign_key_if_exists($prefix . 'equipment_quotation_charges', 'fk_quotation_charges_quotation');
        }

        // Rename tables (reverse order - children first)
        if ($CI->db->table_exists($prefix . 'equipment_quotation_charges')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_quotation_charges`
                TO `{$prefix}equipment_client_quotation_charges`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_pricing_tiers')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_quotation_pricing_tiers`
                TO `{$prefix}equipment_client_quotation_pricing_tiers`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_items')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_quotation_items`
                TO `{$prefix}equipment_client_quotation_items`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotations')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_quotations`
                TO `{$prefix}equipment_client_quotations`");
        }

        // Re-add foreign key constraints with new table names
        if ($CI->db->table_exists($prefix . 'equipment_client_quotations')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotations`
                ADD CONSTRAINT `fk_client_quotations_client` FOREIGN KEY (`client_id`)
                    REFERENCES `{$prefix}clients` (`userid`) ON DELETE RESTRICT,
                ADD CONSTRAINT `fk_client_quotations_agreement` FOREIGN KEY (`agreement_id`)
                    REFERENCES `{$prefix}equipment_agreements` (`id`) ON DELETE SET NULL");
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_items')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotation_items`
                ADD CONSTRAINT `fk_client_quotation_items_quotation` FOREIGN KEY (`quotation_id`)
                    REFERENCES `{$prefix}equipment_client_quotations` (`id`) ON DELETE CASCADE,
                ADD CONSTRAINT `fk_client_quotation_items_equipment` FOREIGN KEY (`equipment_id`)
                    REFERENCES `{$prefix}equipments` (`id`) ON DELETE RESTRICT");
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_pricing_tiers')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotation_pricing_tiers`
                ADD CONSTRAINT `fk_client_quotation_pricing_tier_item` FOREIGN KEY (`quotation_item_id`)
                    REFERENCES `{$prefix}equipment_client_quotation_items` (`id`) ON DELETE CASCADE");
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_charges')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotation_charges`
                ADD CONSTRAINT `fk_client_quotation_charges_quotation` FOREIGN KEY (`quotation_id`)
                    REFERENCES `{$prefix}equipment_client_quotations` (`id`) ON DELETE CASCADE");
        }

        // =====================================================================
        // STEP 2: Create SUPPLIER quotations tables (Migration 134 schema)
        // =====================================================================

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

        log_activity('Equipment Module: Migration 136 completed - Resolved quotations table naming conflict');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // =====================================================================
        // STEP 1: Drop SUPPLIER quotations tables (reverse of Migration 134)
        // =====================================================================

        if ($CI->db->table_exists($prefix . 'equipment_quotation_items')) {
            $CI->db->query("DROP TABLE `{$prefix}equipment_quotation_items`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotations')) {
            $CI->db->query("DROP TABLE `{$prefix}equipment_quotations`");
        }

        // =====================================================================
        // STEP 2: Rename CLIENT quotations tables back to original names
        // =====================================================================

        // Drop foreign key constraints first
        if ($CI->db->table_exists($prefix . 'equipment_client_quotations')) {
            $this->drop_foreign_key_if_exists($prefix . 'equipment_client_quotations', 'fk_client_quotations_client');
            $this->drop_foreign_key_if_exists($prefix . 'equipment_client_quotations', 'fk_client_quotations_agreement');
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_items')) {
            $this->drop_foreign_key_if_exists($prefix . 'equipment_client_quotation_items', 'fk_client_quotation_items_quotation');
            $this->drop_foreign_key_if_exists($prefix . 'equipment_client_quotation_items', 'fk_client_quotation_items_equipment');
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_pricing_tiers')) {
            $this->drop_foreign_key_if_exists($prefix . 'equipment_client_quotation_pricing_tiers', 'fk_client_quotation_pricing_tier_item');
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_charges')) {
            $this->drop_foreign_key_if_exists($prefix . 'equipment_client_quotation_charges', 'fk_client_quotation_charges_quotation');
        }

        // Rename tables back (parent first this time)
        if ($CI->db->table_exists($prefix . 'equipment_client_quotations')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_client_quotations`
                TO `{$prefix}equipment_quotations`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_items')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_client_quotation_items`
                TO `{$prefix}equipment_quotation_items`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_pricing_tiers')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_client_quotation_pricing_tiers`
                TO `{$prefix}equipment_quotation_pricing_tiers`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_client_quotation_charges')) {
            $CI->db->query("RENAME TABLE `{$prefix}equipment_client_quotation_charges`
                TO `{$prefix}equipment_quotation_charges`");
        }

        // Re-add original foreign key constraints
        if ($CI->db->table_exists($prefix . 'equipment_quotations')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_quotations`
                ADD CONSTRAINT `fk_quotations_client` FOREIGN KEY (`client_id`)
                    REFERENCES `{$prefix}clients` (`userid`) ON DELETE RESTRICT,
                ADD CONSTRAINT `fk_quotations_agreement` FOREIGN KEY (`agreement_id`)
                    REFERENCES `{$prefix}equipment_agreements` (`id`) ON DELETE SET NULL");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_items')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_quotation_items`
                ADD CONSTRAINT `fk_quotation_items_quotation` FOREIGN KEY (`quotation_id`)
                    REFERENCES `{$prefix}equipment_quotations` (`id`) ON DELETE CASCADE,
                ADD CONSTRAINT `fk_quotation_items_equipment` FOREIGN KEY (`equipment_id`)
                    REFERENCES `{$prefix}equipments` (`id`) ON DELETE RESTRICT");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_pricing_tiers')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_quotation_pricing_tiers`
                ADD CONSTRAINT `fk_quotation_pricing_tier_item` FOREIGN KEY (`quotation_item_id`)
                    REFERENCES `{$prefix}equipment_quotation_items` (`id`) ON DELETE CASCADE");
        }

        if ($CI->db->table_exists($prefix . 'equipment_quotation_charges')) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_quotation_charges`
                ADD CONSTRAINT `fk_quotation_charges_quotation` FOREIGN KEY (`quotation_id`)
                    REFERENCES `{$prefix}equipment_quotations` (`id`) ON DELETE CASCADE");
        }

        log_activity('Equipment Module: Migration 136 rolled back - Reverted quotations table rename');
    }

    /**
     * Helper function to drop foreign key constraint if it exists
     * @param string $table Table name (without prefix)
     * @param string $constraint Constraint name
     */
    private function drop_foreign_key_if_exists($table, $constraint)
    {
        $CI = &get_instance();

        // Check if constraint exists using information_schema
        $result = $CI->db->query("
            SELECT COUNT(*) as count
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = '{$table}'
            AND CONSTRAINT_NAME = '{$constraint}'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ")->row();

        // If constraint exists, drop it
        if ($result && $result->count > 0) {
            $CI->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        }
    }
}
