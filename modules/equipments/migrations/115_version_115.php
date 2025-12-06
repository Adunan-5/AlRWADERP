<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment_agreement_pricing_tiers table
        // This is the KEY feature allowing tiered pricing (e.g., months 1-12 @ 55k, month 13+ @ 85k)
        if (!$CI->db->table_exists(db_prefix() . 'equipment_agreement_pricing_tiers')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_agreement_pricing_tiers` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `agreement_item_id` INT(11) NOT NULL,
                `from_month` INT(11) NOT NULL COMMENT \'Starting month number (e.g., 1, 7, 13)\',
                `to_month` INT(11) NULL COMMENT \'Ending month number (e.g., 6, 12, 18). NULL = indefinite/open-ended\',
                `monthly_rate` DECIMAL(12,2) NOT NULL COMMENT \'Monthly rental rate for this tier\',
                `currency` VARCHAR(10) DEFAULT \'SAR\',
                `notes` TEXT NULL COMMENT \'Additional notes for this tier\',
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_agreement_item_id` (`agreement_item_id`),
                KEY `idx_month_range` (`from_month`, `to_month`),
                CONSTRAINT `fk_pricing_tiers_agreement_item` FOREIGN KEY (`agreement_item_id`)
                    REFERENCES `' . db_prefix() . 'equipment_agreement_items` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop equipment_agreement_pricing_tiers table
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_agreement_pricing_tiers`;');
    }
}
