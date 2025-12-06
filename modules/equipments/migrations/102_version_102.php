<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create document types tables
 * Version: 1.0.2
 * Description: Create document type masters for equipment and operators
 */
class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment document types table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_document_types')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_document_types` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `name_arabic` VARCHAR(255) DEFAULT NULL,
                `requires_expiry` TINYINT(1) DEFAULT 1,
                `reminder_days` INT(11) DEFAULT 30 COMMENT "Days before expiry to send reminder",
                `is_mandatory` TINYINT(1) DEFAULT 0,
                `display_order` INT(11) DEFAULT 0,
                `active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');

            // Insert default equipment document types
            $CI->db->query("INSERT INTO `" . db_prefix() . "equipment_document_types`
                (`name`, `name_arabic`, `requires_expiry`, `reminder_days`, `is_mandatory`, `display_order`, `created_at`) VALUES
                ('Insurance', 'تأمين', 1, 30, 1, 1, NOW()),
                ('MVPI (Motor Vehicle Periodic Inspection)', 'فحص دوري للمركبات', 1, 30, 1, 2, NOW()),
                ('TAMM Paper', 'ورقة تمم', 1, 30, 1, 3, NOW()),
                ('Istimara (Vehicle Registration)', 'استمارة', 1, 30, 1, 4, NOW()),
                ('Operation Manual', 'دليل التشغيل', 0, 0, 0, 5, NOW()),
                ('Warranty Certificate', 'شهادة الضمان', 1, 60, 0, 6, NOW())
            ");
        }

        // Create operator document types table
        if (!$CI->db->table_exists(db_prefix() . 'operator_document_types')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'operator_document_types` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `name_arabic` VARCHAR(255) DEFAULT NULL,
                `requires_expiry` TINYINT(1) DEFAULT 1,
                `reminder_days` INT(11) DEFAULT 30 COMMENT "Days before expiry to send reminder",
                `is_mandatory` TINYINT(1) DEFAULT 0,
                `display_order` INT(11) DEFAULT 0,
                `active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');

            // Insert default operator document types
            $CI->db->query("INSERT INTO `" . db_prefix() . "operator_document_types`
                (`name`, `name_arabic`, `requires_expiry`, `reminder_days`, `is_mandatory`, `display_order`, `created_at`) VALUES
                ('Iqama (Residence Permit)', 'إقامة', 1, 30, 1, 1, NOW()),
                ('Muqueen (Profession)', 'مهنة', 1, 30, 1, 2, NOW()),
                ('Driving License', 'رخصة قيادة', 1, 30, 1, 3, NOW()),
                ('Passport', 'جواز سفر', 1, 60, 1, 4, NOW()),
                ('Medical Certificate', 'شهادة طبية', 1, 90, 1, 5, NOW()),
                ('Training Certificate', 'شهادة تدريب', 0, 0, 0, 6, NOW()),
                ('Police Clearance', 'حسن سيرة وسلوك', 1, 180, 0, 7, NOW())
            ");
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_document_types')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_document_types`');
        }

        if ($CI->db->table_exists(db_prefix() . 'operator_document_types')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'operator_document_types`');
        }
    }
}
