<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_125 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create quotation pricing tiers table
        $CI->db->query("
            CREATE TABLE IF NOT EXISTS `" . db_prefix() . "equipment_quotation_pricing_tiers` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `quotation_item_id` INT(11) NOT NULL COMMENT 'Reference to equipment_quotation_items',
                `from_month` INT(11) NOT NULL COMMENT 'Starting month number (e.g., 1, 7, 13)',
                `to_month` INT(11) NULL COMMENT 'Ending month number. NULL = indefinite/ongoing',
                `monthly_rate` DECIMAL(12,2) NOT NULL COMMENT 'Monthly rental rate for this tier',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `quotation_item_id` (`quotation_item_id`),
                KEY `from_month` (`from_month`),
                KEY `to_month` (`to_month`),
                CONSTRAINT `fk_quotation_pricing_tiers_item` FOREIGN KEY (`quotation_item_id`)
                    REFERENCES `" . db_prefix() . "equipment_quotation_items` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
        ");
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop quotation pricing tiers table
        $CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "equipment_quotation_pricing_tiers`;");
    }
}
