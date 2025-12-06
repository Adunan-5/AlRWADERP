<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create equipment-operator assignment table
 * Version: 1.0.4
 * Description: Link equipment to operators with assignment tracking
 */
class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment-operator assignment table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_operators')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_operators` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `equipment_id` INT(11) NOT NULL,
                `operator_id` INT(11) NOT NULL,
                `assignment_type` ENUM("primary","secondary","relief") DEFAULT "primary",
                `start_date` DATE NOT NULL,
                `end_date` DATE DEFAULT NULL,
                `mobilization_id` INT(11) DEFAULT NULL COMMENT "Link to client mobilization",
                `status` ENUM("active","inactive","terminated") DEFAULT "active",
                `notes` TEXT DEFAULT NULL,
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_equipment_id` (`equipment_id`),
                KEY `idx_operator_id` (`operator_id`),
                KEY `idx_status` (`status`),
                KEY `idx_assignment_type` (`assignment_type`),
                CONSTRAINT `fk_equipment_operators_equipment`
                    FOREIGN KEY (`equipment_id`) REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_equipment_operators_operator`
                    FOREIGN KEY (`operator_id`) REFERENCES `' . db_prefix() . 'operators` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_operators')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_operators`');
        }
    }
}
