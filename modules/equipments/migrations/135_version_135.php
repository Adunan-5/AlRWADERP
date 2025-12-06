<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_135 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add quotation_id column to equipment_purchase_orders table
        // This links PO back to the source quotation it was converted from
        if (!$CI->db->field_exists('quotation_id', db_prefix() . 'equipment_purchase_orders')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_purchase_orders`
                ADD COLUMN `quotation_id`
                INT(11) NULL
                COMMENT "FK to equipment_quotations table (NULL if PO was not converted from quotation)"
                AFTER `agreement_id`');

            // Add index for quotation_id
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_purchase_orders`
                ADD KEY `quotation_id` (`quotation_id`)');

            // Add foreign key constraint
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_purchase_orders`
                ADD CONSTRAINT `fk_po_quotation`
                FOREIGN KEY (`quotation_id`)
                REFERENCES `' . db_prefix() . 'equipment_quotations` (`id`)
                ON DELETE SET NULL');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Remove quotation_id column (drop FK constraint first)
        if ($CI->db->field_exists('quotation_id', db_prefix() . 'equipment_purchase_orders')) {
            // Drop foreign key constraint first
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_purchase_orders`
                DROP FOREIGN KEY `fk_po_quotation`');

            // Then drop the column (index will be dropped automatically)
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'equipment_purchase_orders`
                DROP COLUMN `quotation_id`');
        }
    }
}
