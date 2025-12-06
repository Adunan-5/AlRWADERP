<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Fix equipment_po_charges table structure
        if ($CI->db->table_exists(db_prefix() . 'equipment_po_charges')) {
            // Drop and recreate with correct structure
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_po_charges`');

            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_po_charges` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `po_id` INT(11) NOT NULL COMMENT "FK to equipment_purchase_orders",
                `charge_type` ENUM("mobilization", "demobilization", "setup", "teardown", "transportation", "other") NOT NULL COMMENT "Type of charge",
                `charge_name` VARCHAR(255) NOT NULL COMMENT "Charge description/name",
                `amount` DECIMAL(12,2) NOT NULL COMMENT "Charge amount",
                `charge_date` DATE NULL COMMENT "Date when charge is applicable",
                `status` ENUM("pending", "invoiced", "paid", "waived", "cancelled") NOT NULL DEFAULT "pending" COMMENT "Charge status",
                `notes` TEXT NULL COMMENT "Additional notes about the charge",
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `po_id` (`po_id`),
                KEY `charge_type` (`charge_type`),
                KEY `status` (`status`),
                CONSTRAINT `fk_po_charges_po` FOREIGN KEY (`po_id`)
                    REFERENCES `' . db_prefix() . 'equipment_purchase_orders` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_po_charges')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_po_charges`');
        }
    }
}
