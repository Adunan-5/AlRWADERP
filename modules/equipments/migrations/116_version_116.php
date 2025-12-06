<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_116 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment_agreement_charges table
        // For one-time charges like mobilization and demobilization
        if (!$CI->db->table_exists(db_prefix() . 'equipment_agreement_charges')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_agreement_charges` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `agreement_id` INT(11) NOT NULL,
                `agreement_item_id` INT(11) NULL COMMENT \'Link to specific equipment item, NULL if applies to entire agreement\',
                `charge_type` ENUM(\'mobilization\',\'demobilization\',\'setup\',\'teardown\',\'other\') NOT NULL,
                `charge_name` VARCHAR(255) NOT NULL COMMENT \'Display name for charge\',
                `amount` DECIMAL(12,2) NOT NULL,
                `currency` VARCHAR(10) DEFAULT \'SAR\',
                `status` ENUM(\'pending\',\'invoiced\',\'paid\',\'waived\',\'cancelled\') DEFAULT \'pending\',
                `invoice_id` INT(11) NULL COMMENT \'Link to tblpayments if invoiced\',
                `charge_date` DATE NULL COMMENT \'Date when charge was applied\',
                `notes` TEXT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_agreement_id` (`agreement_id`),
                KEY `idx_agreement_item_id` (`agreement_item_id`),
                KEY `idx_charge_type` (`charge_type`),
                KEY `idx_status` (`status`),
                CONSTRAINT `fk_agreement_charges_agreement` FOREIGN KEY (`agreement_id`)
                    REFERENCES `' . db_prefix() . 'equipment_agreements` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_agreement_charges_item` FOREIGN KEY (`agreement_item_id`)
                    REFERENCES `' . db_prefix() . 'equipment_agreement_items` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop equipment_agreement_charges table
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_agreement_charges`;');
    }
}
