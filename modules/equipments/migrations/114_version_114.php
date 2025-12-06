<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_114 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment_agreement_items table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_agreement_items')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_agreement_items` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `agreement_id` INT(11) NOT NULL,
                `equipment_id` INT(11) NOT NULL,
                `equipment_description` TEXT NULL COMMENT \'Description at time of agreement\',
                `standard_hours_per_day` DECIMAL(5,2) DEFAULT 10.00 COMMENT \'Standard working hours per day\',
                `days_per_month` INT(11) DEFAULT 26 COMMENT \'Standard working days per month\',
                `total_hours_per_month` DECIMAL(6,2) DEFAULT 260.00 COMMENT \'Standard hours per month (hours_per_day * days_per_month)\',
                `overtime_calculation` ENUM(\'pro_rata\',\'fixed_rate\',\'none\') DEFAULT \'pro_rata\',
                `overtime_rate_multiplier` DECIMAL(4,2) DEFAULT 1.00 COMMENT \'Multiplier for overtime (e.g., 1.5 for time-and-a-half)\',
                `notes` TEXT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_agreement_id` (`agreement_id`),
                KEY `idx_equipment_id` (`equipment_id`),
                CONSTRAINT `fk_agreement_items_agreement` FOREIGN KEY (`agreement_id`)
                    REFERENCES `' . db_prefix() . 'equipment_agreements` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_agreement_items_equipment` FOREIGN KEY (`equipment_id`)
                    REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop equipment_agreement_items table
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_agreement_items`;');
    }
}
