<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add supplier_id column to equipments table
        if (!$CI->db->field_exists('supplier_id', db_prefix() . 'equipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipments`
                ADD COLUMN `supplier_id` INT(11) NULL AFTER `ownership_type`,
                ADD KEY `idx_supplier_id` (`supplier_id`)
            ');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Remove supplier_id column from equipments table
        if ($CI->db->field_exists('supplier_id', db_prefix() . 'equipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipments` DROP COLUMN `supplier_id`');
        }
    }
}
