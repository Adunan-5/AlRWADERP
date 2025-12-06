<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 139: Create Sales Orders Tables
 *
 * Purpose: Implement Sales Orders workflow between Client Quotations and Agreements
 * Flow: Client Quotation → Sales Order → Agreement → Mobilization
 *
 * Sales Order represents a confirmed order from client after quotation acceptance.
 * It tracks order fulfillment, links to agreements, and manages the sales pipeline.
 */
class Migration_Version_139 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // Create equipment_sales_orders table
        if (!$CI->db->table_exists($prefix . 'equipment_sales_orders')) {
            $CI->db->query("CREATE TABLE `{$prefix}equipment_sales_orders` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `order_number` VARCHAR(50) NOT NULL UNIQUE,
                `quotation_id` INT(11) NULL,
                `client_id` INT(11) NOT NULL,
                `project_id` INT(11) NULL,
                `order_date` DATE NOT NULL,
                `expected_delivery_date` DATE NULL,
                `payment_terms_days` INT(11) NOT NULL DEFAULT 30,
                `currency` VARCHAR(10) NOT NULL DEFAULT 'SAR',
                `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 15.00,
                `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `status` ENUM('draft', 'confirmed', 'partially_fulfilled', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'draft',
                `fulfillment_status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
                `terms_conditions` TEXT NULL,
                `notes` TEXT NULL,
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_order_number` (`order_number`),
                KEY `idx_quotation` (`quotation_id`),
                KEY `idx_client` (`client_id`),
                KEY `idx_project` (`project_id`),
                KEY `idx_status` (`status`),
                KEY `idx_order_date` (`order_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // Create equipment_sales_order_items table
        if (!$CI->db->table_exists($prefix . 'equipment_sales_order_items')) {
            $CI->db->query("CREATE TABLE `{$prefix}equipment_sales_order_items` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `sales_order_id` INT(11) NOT NULL,
                `equipment_id` INT(11) NOT NULL,
                `operator_id` INT(11) NULL,
                `quantity` INT(11) NOT NULL DEFAULT 1,
                `rental_period_months` INT(11) NOT NULL DEFAULT 1,
                `unit_rate` DECIMAL(15,2) NOT NULL,
                `line_total` DECIMAL(15,2) NOT NULL,
                `fulfillment_status` ENUM('pending', 'partially_fulfilled', 'fulfilled') NOT NULL DEFAULT 'pending',
                `fulfilled_quantity` INT(11) NOT NULL DEFAULT 0,
                `notes` TEXT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_sales_order` (`sales_order_id`),
                KEY `idx_equipment` (`equipment_id`),
                KEY `idx_operator` (`operator_id`),
                KEY `idx_fulfillment` (`fulfillment_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // Create equipment_sales_order_fulfillments table (tracks mobilizations linked to orders)
        if (!$CI->db->table_exists($prefix . 'equipment_sales_order_fulfillments')) {
            $CI->db->query("CREATE TABLE `{$prefix}equipment_sales_order_fulfillments` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `sales_order_id` INT(11) NOT NULL,
                `sales_order_item_id` INT(11) NOT NULL,
                `mobilization_id` INT(11) NULL,
                `agreement_id` INT(11) NULL,
                `fulfilled_date` DATE NOT NULL,
                `fulfilled_quantity` INT(11) NOT NULL DEFAULT 1,
                `notes` TEXT NULL,
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_sales_order` (`sales_order_id`),
                KEY `idx_order_item` (`sales_order_item_id`),
                KEY `idx_mobilization` (`mobilization_id`),
                KEY `idx_agreement` (`agreement_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // Add sales_order_id column to agreements table
        $fields = $CI->db->list_fields($prefix . 'equipment_agreements');
        if (!in_array('sales_order_id', $fields)) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_agreements`
                ADD COLUMN `sales_order_id` INT(11) NULL AFTER `agreement_number`,
                ADD KEY `idx_sales_order` (`sales_order_id`)");
        }

        // Add sales_order_id column to client quotations table
        $fields = $CI->db->list_fields($prefix . 'equipment_client_quotations');
        if (!in_array('sales_order_id', $fields)) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotations`
                ADD COLUMN `sales_order_id` INT(11) NULL AFTER `agreement_id`,
                ADD KEY `idx_sales_order` (`sales_order_id`)");
        }

        log_activity('Equipment Module: Migration 139 completed - Created Sales Orders tables');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // Remove sales_order_id from client quotations
        $fields = $CI->db->list_fields($prefix . 'equipment_client_quotations');
        if (in_array('sales_order_id', $fields)) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotations`
                DROP COLUMN `sales_order_id`");
        }

        // Remove sales_order_id from agreements
        $fields = $CI->db->list_fields($prefix . 'equipment_agreements');
        if (in_array('sales_order_id', $fields)) {
            $CI->db->query("ALTER TABLE `{$prefix}equipment_agreements`
                DROP COLUMN `sales_order_id`");
        }

        // Drop tables in reverse order
        if ($CI->db->table_exists($prefix . 'equipment_sales_order_fulfillments')) {
            $CI->db->query("DROP TABLE `{$prefix}equipment_sales_order_fulfillments`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_sales_order_items')) {
            $CI->db->query("DROP TABLE `{$prefix}equipment_sales_order_items`");
        }

        if ($CI->db->table_exists($prefix . 'equipment_sales_orders')) {
            $CI->db->query("DROP TABLE `{$prefix}equipment_sales_orders`");
        }

        log_activity('Equipment Module: Migration 139 rolled back - Removed Sales Orders tables');
    }
}
