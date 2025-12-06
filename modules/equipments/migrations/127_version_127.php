<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add all missing columns to equipment_po_items table
        if ($CI->db->table_exists(db_prefix() . 'equipment_po_items')) {
            $columns_to_add = [
                'equipment_description' => 'VARCHAR(500) NOT NULL COMMENT "Equipment description/specification" AFTER `equipment_id`',
                'quantity' => 'INT(11) NOT NULL DEFAULT 1 COMMENT "Number of units" AFTER `equipment_description`',
                'unit' => 'VARCHAR(50) NOT NULL DEFAULT "unit" COMMENT "Unit of measure" AFTER `quantity`',
                'standard_hours_per_day' => 'DECIMAL(5,2) NULL COMMENT "Standard working hours per day" AFTER `unit`',
                'days_per_month' => 'INT(11) NULL COMMENT "Expected working days per month" AFTER `standard_hours_per_day`',
                'overtime_rate_multiplier' => 'DECIMAL(5,2) NULL DEFAULT 1.5 COMMENT "Overtime rate multiplier" AFTER `days_per_month`',
                'notes' => 'TEXT NULL COMMENT "Item-specific notes" AFTER `overtime_rate_multiplier`',
            ];

            foreach ($columns_to_add as $column => $definition) {
                if (!$CI->db->field_exists($column, db_prefix() . 'equipment_po_items')) {
                    $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items` ADD COLUMN `' . $column . '` ' . $definition);
                }
            }
        }
    }

    public function down()
    {
        $CI = &get_instance();

        $columns_to_remove = [
            'equipment_description',
            'quantity',
            'unit',
            'standard_hours_per_day',
            'days_per_month',
            'overtime_rate_multiplier',
            'notes'
        ];

        foreach ($columns_to_remove as $column) {
            if ($CI->db->field_exists($column, db_prefix() . 'equipment_po_items')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items` DROP COLUMN `' . $column . '`');
            }
        }
    }
}
