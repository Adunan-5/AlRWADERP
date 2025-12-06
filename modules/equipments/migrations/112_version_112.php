<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment_documents table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_documents')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_documents` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `equipment_id` INT(11) NOT NULL,
                `document_type_id` INT(11) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL DEFAULT "",
                `file_path` VARCHAR(500) NOT NULL DEFAULT "",
                `file_size` INT(11) DEFAULT NULL,
                `document_number` VARCHAR(100) DEFAULT NULL,
                `issue_date` DATE DEFAULT NULL,
                `expiry_date` DATE DEFAULT NULL,
                `reminder_sent` TINYINT(1) DEFAULT 0,
                `reminder_sent_date` DATETIME DEFAULT NULL,
                `notes` TEXT DEFAULT NULL,
                `uploaded_by` INT(11) NOT NULL,
                `uploaded_at` DATETIME NOT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_equipment_id` (`equipment_id`),
                KEY `idx_document_type_id` (`document_type_id`),
                KEY `idx_expiry_date` (`expiry_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop equipment_documents table
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_documents`;');
    }
}
