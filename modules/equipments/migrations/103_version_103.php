<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create documents storage tables
 * Version: 1.0.3
 * Description: Create tables to store uploaded documents for equipment and operators
 */
class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment documents table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_documents')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_documents` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `equipment_id` INT(11) NOT NULL,
                `document_type_id` INT(11) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(500) NOT NULL,
                `file_size` INT(11) DEFAULT NULL COMMENT "File size in bytes",
                `document_number` VARCHAR(100) DEFAULT NULL COMMENT "Document reference number",
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
                KEY `idx_expiry_date` (`expiry_date`),
                CONSTRAINT `fk_equipment_documents_equipment`
                    FOREIGN KEY (`equipment_id`) REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_equipment_documents_type`
                    FOREIGN KEY (`document_type_id`) REFERENCES `' . db_prefix() . 'equipment_document_types` (`id`) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }

        // Create operator documents table
        if (!$CI->db->table_exists(db_prefix() . 'operator_documents')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'operator_documents` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `operator_id` INT(11) NOT NULL,
                `document_type_id` INT(11) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(500) NOT NULL,
                `file_size` INT(11) DEFAULT NULL COMMENT "File size in bytes",
                `document_number` VARCHAR(100) DEFAULT NULL COMMENT "Document reference number",
                `issue_date` DATE DEFAULT NULL,
                `expiry_date` DATE DEFAULT NULL,
                `reminder_sent` TINYINT(1) DEFAULT 0,
                `reminder_sent_date` DATETIME DEFAULT NULL,
                `notes` TEXT DEFAULT NULL,
                `uploaded_by` INT(11) NOT NULL,
                `uploaded_at` DATETIME NOT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_operator_id` (`operator_id`),
                KEY `idx_document_type_id` (`document_type_id`),
                KEY `idx_expiry_date` (`expiry_date`),
                CONSTRAINT `fk_operator_documents_operator`
                    FOREIGN KEY (`operator_id`) REFERENCES `' . db_prefix() . 'operators` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_operator_documents_type`
                    FOREIGN KEY (`document_type_id`) REFERENCES `' . db_prefix() . 'operator_document_types` (`id`) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_documents')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_documents`');
        }

        if ($CI->db->table_exists(db_prefix() . 'operator_documents')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'operator_documents`');
        }
    }
}
