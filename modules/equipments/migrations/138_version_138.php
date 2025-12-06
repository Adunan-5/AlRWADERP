<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 138: Add Missing terms_conditions Column to Client Quotations
 *
 * Issue: Migration 136 renamed the client quotations table but the renamed
 * table is missing the terms_conditions column that existed in the original schema.
 *
 * This migration adds the missing column to equipment_client_quotations table.
 */
class Migration_Version_138 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // Add terms_conditions column if it doesn't exist
        if ($CI->db->table_exists($prefix . 'equipment_client_quotations')) {
            // Check if column already exists
            $fields = $CI->db->list_fields($prefix . 'equipment_client_quotations');

            if (!in_array('terms_conditions', $fields)) {
                $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotations`
                    ADD COLUMN `terms_conditions` TEXT NULL
                    AFTER `status`");

                log_activity('Equipment Module: Migration 138 completed - Added terms_conditions column to client quotations');
            }
        }
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        // Remove terms_conditions column
        if ($CI->db->table_exists($prefix . 'equipment_client_quotations')) {
            $fields = $CI->db->list_fields($prefix . 'equipment_client_quotations');

            if (in_array('terms_conditions', $fields)) {
                $CI->db->query("ALTER TABLE `{$prefix}equipment_client_quotations`
                    DROP COLUMN `terms_conditions`");

                log_activity('Equipment Module: Migration 138 rolled back - Removed terms_conditions column');
            }
        }
    }
}
