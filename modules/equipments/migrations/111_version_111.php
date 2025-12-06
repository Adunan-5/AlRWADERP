<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment_document_types table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_document_types')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_document_types` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `name_arabic` VARCHAR(255) DEFAULT NULL,
                `requires_expiry` TINYINT(1) DEFAULT 1,
                `reminder_days` INT(11) DEFAULT 30,
                `is_mandatory` TINYINT(1) DEFAULT 0,
                `display_order` INT(11) DEFAULT 0,
                `active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        // Insert default equipment document types
        $default_types = [
            [
                'name' => 'Istimara (Vehicle Registration)',
                'name_arabic' => 'استمارة',
                'requires_expiry' => 1,
                'reminder_days' => 30,
                'is_mandatory' => 1,
                'display_order' => 1,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Insurance Certificate',
                'name_arabic' => 'شهادة التأمين',
                'requires_expiry' => 1,
                'reminder_days' => 30,
                'is_mandatory' => 1,
                'display_order' => 2,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Operating License',
                'name_arabic' => 'رخصة التشغيل',
                'requires_expiry' => 1,
                'reminder_days' => 30,
                'is_mandatory' => 0,
                'display_order' => 3,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Safety Inspection',
                'name_arabic' => 'فحص السلامة',
                'requires_expiry' => 1,
                'reminder_days' => 30,
                'is_mandatory' => 0,
                'display_order' => 4,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Maintenance Certificate',
                'name_arabic' => 'شهادة الصيانة',
                'requires_expiry' => 1,
                'reminder_days' => 90,
                'is_mandatory' => 0,
                'display_order' => 5,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($default_types as $type) {
            $CI->db->insert(db_prefix() . 'equipment_document_types', $type);
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop equipment_document_types table
        $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'equipment_document_types`;');
    }
}
