<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_119 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment purchase orders table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_purchase_orders')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_purchase_orders` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `po_number` VARCHAR(50) NOT NULL COMMENT "Auto-generated PO number (e.g., PO-2025-001)",
                `supplier_id` INT(11) NOT NULL COMMENT "FK to suppliers table",
                `agreement_id` INT(11) NULL COMMENT "Optional link to supplier agreement",
                `po_date` DATE NOT NULL COMMENT "Purchase order date",
                `delivery_date` DATE NULL COMMENT "Expected delivery date",
                `validity_date` DATE NULL COMMENT "PO validity/expiry date",
                `payment_terms_days` INT(11) NOT NULL DEFAULT 30 COMMENT "Payment terms in days",
                `currency` VARCHAR(10) NOT NULL DEFAULT "SAR" COMMENT "Currency code",
                `status` ENUM("draft", "sent", "confirmed", "partially_received", "completed", "cancelled") NOT NULL DEFAULT "draft" COMMENT "PO workflow status",
                `notes` TEXT NULL COMMENT "Internal notes",
                `terms_conditions` TEXT NULL COMMENT "Terms and conditions",
                `created_by` INT(11) NOT NULL COMMENT "Staff who created the PO",
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `po_number` (`po_number`),
                KEY `supplier_id` (`supplier_id`),
                KEY `agreement_id` (`agreement_id`),
                KEY `status` (`status`),
                KEY `po_date` (`po_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_purchase_orders')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_purchase_orders`');
        }
    }
}
