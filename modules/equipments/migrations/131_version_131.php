<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_131 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add item_type column to equipment_po_items table
        if (!$CI->db->field_exists('item_type', db_prefix() . 'equipment_po_items')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                ADD COLUMN `item_type`
                ENUM("equipment", "operator", "equipment_with_operator")
                NOT NULL DEFAULT "equipment"
                COMMENT "Type of item: equipment only, operator only, or both"
                AFTER `po_id`');
        }

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

        // Add operator_description column to equipment_po_items table
        if (!$CI->db->field_exists('operator_description', db_prefix() . 'equipment_po_items')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                ADD COLUMN `operator_description`
                VARCHAR(500) NULL
                COMMENT "Operator description when item type includes operator"
                AFTER `equipment_description`');
        }

        // Make equipment_description nullable (since operator-only items won't have equipment)
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
            MODIFY COLUMN `equipment_description`
            VARCHAR(500) NULL
            COMMENT "Equipment description (NULL for operator-only items)"');
    }

    public function down()
    {
        $CI = &get_instance();

        // Remove item_type column
        if ($CI->db->field_exists('item_type', db_prefix() . 'equipment_po_items')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                DROP COLUMN `item_type`');
        }

        // Remove operator_id column (drop FK constraint first)
        if ($CI->db->field_exists('operator_id', db_prefix() . 'equipment_po_items')) {
            // Drop foreign key constraint first
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                DROP FOREIGN KEY `fk_po_items_operator`');

            // Then drop the column (index will be dropped automatically)
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                DROP COLUMN `operator_id`');
        }

        // Remove operator_description column
        if ($CI->db->field_exists('operator_description', db_prefix() . 'equipment_po_items')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
                DROP COLUMN `operator_description`');
        }

        // Revert equipment_description to NOT NULL
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_po_items`
            MODIFY COLUMN `equipment_description`
            VARCHAR(500) NOT NULL
            COMMENT "Equipment description/specification"');
    }
}
