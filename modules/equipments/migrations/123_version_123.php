<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_123 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment quotations table
        $CI->db->query("
            CREATE TABLE IF NOT EXISTS `" . db_prefix() . "equipment_quotations` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `quotation_number` VARCHAR(50) NOT NULL,
                `client_id` INT(11) NOT NULL COMMENT 'Reference to tblclients',
                `agreement_id` INT(11) NULL COMMENT 'Optional reference to equipment_agreements',
                `quotation_date` DATE NOT NULL,
                `validity_date` DATE NULL COMMENT 'Date until quotation is valid',
                `payment_terms_days` INT(11) NOT NULL DEFAULT 30 COMMENT 'Payment terms in days',
                `currency` VARCHAR(10) NOT NULL DEFAULT 'SAR',
                `status` ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') NOT NULL DEFAULT 'draft',
                `terms_conditions` TEXT NULL,
                `notes` TEXT NULL,
                `created_by` INT(11) NOT NULL COMMENT 'Staff member who created',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `quotation_number` (`quotation_number`),
                KEY `client_id` (`client_id`),
                KEY `agreement_id` (`agreement_id`),
                KEY `status` (`status`),
                KEY `quotation_date` (`quotation_date`),
                CONSTRAINT `fk_quotations_client` FOREIGN KEY (`client_id`)
                    REFERENCES `" . db_prefix() . "clients` (`userid`) ON DELETE RESTRICT,
                CONSTRAINT `fk_quotations_agreement` FOREIGN KEY (`agreement_id`)
                    REFERENCES `" . db_prefix() . "equipment_agreements` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
        ");
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop quotations table
        $CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "equipment_quotations`;");
    }
}
