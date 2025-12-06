<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_124 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create quotation items table
        $CI->db->query("
            CREATE TABLE IF NOT EXISTS `" . db_prefix() . "equipment_quotation_items` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `quotation_id` INT(11) NOT NULL COMMENT 'Reference to equipment_quotations',
                `equipment_id` INT(11) NULL COMMENT 'Optional reference to tblequipments',
                `equipment_description` VARCHAR(500) NOT NULL COMMENT 'Equipment name/description',
                `quantity` INT(11) NOT NULL DEFAULT 1,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'unit' COMMENT 'Unit of measurement',
                `standard_hours_per_day` DECIMAL(5,2) NULL COMMENT 'Standard working hours per day',
                `days_per_month` INT(11) NULL COMMENT 'Expected working days per month',
                `notes` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `quotation_id` (`quotation_id`),
                KEY `equipment_id` (`equipment_id`),
                CONSTRAINT `fk_quotation_items_quotation` FOREIGN KEY (`quotation_id`)
                    REFERENCES `" . db_prefix() . "equipment_quotations` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_quotation_items_equipment` FOREIGN KEY (`equipment_id`)
                    REFERENCES `" . db_prefix() . "equipments` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
        ");
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop quotation items table
        $CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "equipment_quotation_items`;");
    }
}
