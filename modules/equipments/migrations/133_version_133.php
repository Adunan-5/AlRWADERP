<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_133 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment_rfq table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_rfq')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_rfq` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `rfq_number` VARCHAR(50) NOT NULL COMMENT "Auto-generated RFQ number (e.g., RFQ-2025-001)",
                `rfq_date` DATE NOT NULL COMMENT "Date RFQ was created",
                `required_by_date` DATE NULL COMMENT "Deadline for supplier responses",
                `project_reference` VARCHAR(255) NULL COMMENT "Project or client reference that triggered this RFQ",
                `client_id` INT(11) NULL COMMENT "FK to clients (optional, which client project is this for)",
                `status` ENUM("draft","sent","responses_received","evaluated","closed","cancelled") NOT NULL DEFAULT "draft" COMMENT "RFQ status",
                `expected_start_date` DATE NULL COMMENT "When equipment/operator is needed",
                `expected_duration_months` INT(11) NULL COMMENT "Expected rental duration in months",
                `terms_conditions` TEXT NULL COMMENT "Terms and conditions for suppliers",
                `notes` TEXT NULL COMMENT "Internal notes",
                `created_by` INT(11) NOT NULL COMMENT "Staff member who created the RFQ",
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `rfq_number` (`rfq_number`),
                KEY `client_id` (`client_id`),
                KEY `status` (`status`),
                KEY `rfq_date` (`rfq_date`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }

        // Create equipment_rfq_items table (similar to PO items)
        if (!$CI->db->table_exists(db_prefix() . 'equipment_rfq_items')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_rfq_items` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `rfq_id` INT(11) NOT NULL COMMENT "FK to equipment_rfq",
                `item_type` ENUM("equipment","operator","equipment_with_operator") NOT NULL DEFAULT "equipment" COMMENT "Type of item being requested",
                `equipment_id` INT(11) NULL COMMENT "FK to equipments table (NULL for unlisted/new equipment)",
                `equipment_description` VARCHAR(500) NULL COMMENT "Equipment description/specification",
                `operator_id` INT(11) NULL COMMENT "FK to operators table (NULL for unlisted/new operators)",
                `operator_description` VARCHAR(500) NULL COMMENT "Operator description/requirements",
                `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT "Number of units requested",
                `unit` VARCHAR(50) NOT NULL DEFAULT "unit" COMMENT "Unit of measure",
                `standard_hours_per_day` DECIMAL(5,2) NULL COMMENT "Expected working hours per day",
                `days_per_month` INT(11) NULL COMMENT "Expected working days per month",
                `expected_duration_months` INT(11) NULL COMMENT "Expected rental duration",
                `notes` TEXT NULL COMMENT "Item-specific notes/requirements",
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `rfq_id` (`rfq_id`),
                KEY `equipment_id` (`equipment_id`),
                KEY `operator_id` (`operator_id`),
                CONSTRAINT `fk_rfq_items_rfq` FOREIGN KEY (`rfq_id`)
                    REFERENCES `' . db_prefix() . 'equipment_rfq` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_rfq_items_equipment` FOREIGN KEY (`equipment_id`)
                    REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE SET NULL,
                CONSTRAINT `fk_rfq_items_operator` FOREIGN KEY (`operator_id`)
                    REFERENCES `' . db_prefix() . 'operators` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }

        // Create equipment_rfq_suppliers table (track which suppliers received the RFQ)
        if (!$CI->db->table_exists(db_prefix() . 'equipment_rfq_suppliers')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_rfq_suppliers` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `rfq_id` INT(11) NOT NULL COMMENT "FK to equipment_rfq",
                `supplier_id` INT(11) NOT NULL COMMENT "FK to suppliers",
                `sent_date` DATE NULL COMMENT "Date RFQ was sent to this supplier",
                `response_status` ENUM("pending","quoted","declined","no_response") NOT NULL DEFAULT "pending" COMMENT "Supplier response status",
                `response_received_date` DATE NULL COMMENT "Date supplier responded",
                `notes` TEXT NULL COMMENT "Notes about this supplier response",
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `rfq_id` (`rfq_id`),
                KEY `supplier_id` (`supplier_id`),
                KEY `response_status` (`response_status`),
                UNIQUE KEY `rfq_supplier_unique` (`rfq_id`, `supplier_id`),
                CONSTRAINT `fk_rfq_suppliers_rfq` FOREIGN KEY (`rfq_id`)
                    REFERENCES `' . db_prefix() . 'equipment_rfq` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop tables in reverse order (respecting foreign key constraints)
        if ($CI->db->table_exists(db_prefix() . 'equipment_rfq_suppliers')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_rfq_suppliers`');
        }

        if ($CI->db->table_exists(db_prefix() . 'equipment_rfq_items')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_rfq_items`');
        }

        if ($CI->db->table_exists(db_prefix() . 'equipment_rfq')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_rfq`');
        }
    }
}
