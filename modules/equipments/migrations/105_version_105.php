<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create equipment mobilization table
 * Version: 1.0.5
 * Description: Track equipment deployment to client sites
 */
class Migration_Version_105 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment mobilization table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_mobilization')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_mobilization` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `mobilization_number` VARCHAR(100) DEFAULT NULL,
                `equipment_id` INT(11) NOT NULL,
                `operator_id` INT(11) DEFAULT NULL,
                `client_id` INT(11) NOT NULL COMMENT "From tblclients",
                `project_id` INT(11) DEFAULT NULL COMMENT "From tblprojects",
                `quotation_id` INT(11) DEFAULT NULL COMMENT "From tblestimates",
                `location` VARCHAR(255) DEFAULT NULL,
                `mobilization_date` DATE DEFAULT NULL,
                `demobilization_date` DATE DEFAULT NULL,
                `planned_return_date` DATE DEFAULT NULL,
                `actual_return_date` DATE DEFAULT NULL,
                `status` ENUM("planned","mobilized","active","demobilized","completed") DEFAULT "planned",
                `hourly_rate` DECIMAL(15,2) DEFAULT NULL,
                `daily_rate` DECIMAL(15,2) DEFAULT NULL,
                `monthly_rate` DECIMAL(15,2) DEFAULT NULL,
                `billing_type` ENUM("hourly","daily","monthly","fixed") DEFAULT "hourly",
                `remarks` TEXT DEFAULT NULL,
                `mobilization_cost` DECIMAL(15,2) DEFAULT 0 COMMENT "One-time mobilization cost",
                `demobilization_cost` DECIMAL(15,2) DEFAULT 0 COMMENT "One-time demobilization cost",
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `mobilization_number` (`mobilization_number`),
                KEY `idx_equipment_id` (`equipment_id`),
                KEY `idx_operator_id` (`operator_id`),
                KEY `idx_client_id` (`client_id`),
                KEY `idx_project_id` (`project_id`),
                KEY `idx_status` (`status`),
                CONSTRAINT `fk_equipment_mobilization_equipment`
                    FOREIGN KEY (`equipment_id`) REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_equipment_mobilization_operator`
                    FOREIGN KEY (`operator_id`) REFERENCES `' . db_prefix() . 'operators` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_mobilization')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_mobilization`');
        }
    }
}
