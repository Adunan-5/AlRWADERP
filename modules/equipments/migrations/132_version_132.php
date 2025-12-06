<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_132 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add operator_id column (FK to operators table)
        if (!$CI->db->field_exists('operator_id', db_prefix() . 'equipment_po_items')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                ADD COLUMN `operator_id`
                INT(11) NULL
                COMMENT "FK to operators table (NULL for unlisted/new operators)"
                AFTER `equipment_id`');

            // Add index for operator_id
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                ADD KEY `operator_id` (`operator_id`)');

            // Add foreign key constraint
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                ADD CONSTRAINT `fk_po_items_operator`
                FOREIGN KEY (`operator_id`)
                REFERENCES `' . db_prefix() . 'operators` (`id`)
                ON DELETE SET NULL');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Remove operator_id column (drop FK constraint first)
        if ($CI->db->field_exists('operator_id', db_prefix() . 'equipment_po_items')) {
            // Drop foreign key constraint first
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                DROP FOREIGN KEY `fk_po_items_operator`');

            // Then drop the column (index will be dropped automatically)
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                DROP COLUMN `operator_id`');
        }
    }
}
