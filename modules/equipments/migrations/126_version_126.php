<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_126 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create quotation charges table
        $CI->db->query("
            CREATE TABLE IF NOT EXISTS `" . db_prefix() . "equipment_quotation_charges` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `quotation_id` INT(11) NOT NULL COMMENT 'Reference to equipment_quotations',
                `charge_type` ENUM('mobilization', 'demobilization', 'setup', 'teardown', 'transportation', 'other') NOT NULL,
                `charge_name` VARCHAR(255) NOT NULL COMMENT 'Display name of the charge',
                `amount` DECIMAL(12,2) NOT NULL COMMENT 'Charge amount',
                `notes` TEXT NULL,
                `status` ENUM('pending', 'invoiced', 'paid', 'waived', 'cancelled') NOT NULL DEFAULT 'pending',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `quotation_id` (`quotation_id`),
                KEY `charge_type` (`charge_type`),
                KEY `status` (`status`),
                CONSTRAINT `fk_quotation_charges_quotation` FOREIGN KEY (`quotation_id`)
                    REFERENCES `" . db_prefix() . "equipment_quotations` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
        ");
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop quotation charges table
        $CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "equipment_quotation_charges`;");
    }
}
