<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_334 extends App_Migration
{
    public function up()
    {
        // Create document_types table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "document_types` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT NULL,
            `is_system` TINYINT(1) DEFAULT 0 COMMENT '1=System defined, cannot be deleted',
            `created_at` DATETIME NULL,
            `modified_at` DATETIME NULL,
            `created_by` INT(11) NULL,
            `modified_by` INT(11) NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $this->db->char_set . ";");

        // Insert default document types
        $default_types = [
            ['name' => 'Passport', 'description' => 'Passport documents', 'is_system' => 1],
            ['name' => 'Iqama', 'description' => 'Iqama/Residence permit documents', 'is_system' => 1],
            ['name' => 'Educational Certificate', 'description' => 'Educational certificates and diplomas', 'is_system' => 1],
            ['name' => 'Driver License', 'description' => 'Driving license documents', 'is_system' => 1],
            ['name' => 'Contract', 'description' => 'Employment contracts', 'is_system' => 1],
            ['name' => 'Visa', 'description' => 'Visa documents', 'is_system' => 1],
            ['name' => 'Medical Certificate', 'description' => 'Medical certificates and health cards', 'is_system' => 1],
            ['name' => 'Insurance', 'description' => 'Insurance documents', 'is_system' => 1],
            ['name' => 'Bank Details', 'description' => 'Bank account documents', 'is_system' => 1],
            ['name' => 'ID Card', 'description' => 'National ID card or other identification', 'is_system' => 1],
            ['name' => 'Training Certificate', 'description' => 'Training and professional certificates', 'is_system' => 1],
            ['name' => 'Aramco ID', 'description' => 'Aramco identification documents', 'is_system' => 1],
            ['name' => 'Work Permit', 'description' => 'Work permits and authorizations', 'is_system' => 1],
            ['name' => 'Other', 'description' => 'Other document types', 'is_system' => 1]
        ];

        foreach ($default_types as $type) {
            $type['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'document_types', $type);
        }

        // Update existing staff_files table to add document_type_id if it doesn't exist
        if (!$this->db->field_exists('document_type_id', db_prefix() . 'staff_files')) {
            $this->db->query("ALTER TABLE `" . db_prefix() . "staff_files`
                ADD COLUMN `document_type_id` INT(11) NULL AFTER `staff_id`,
                ADD INDEX `document_type_id` (`document_type_id`)");
        }

        // Migrate existing document_type text values to the new document_types table
        // This will create entries for any existing document types that aren't in the default list
        $existing_files = $this->db->query("SELECT DISTINCT document_type FROM " . db_prefix() . "staff_files WHERE document_type IS NOT NULL AND document_type != ''")->result();

        foreach ($existing_files as $file) {
            // Check if this document type already exists
            $exists = $this->db->where('name', $file->document_type)->get(db_prefix() . 'document_types')->row();

            if (!$exists) {
                $this->db->insert(db_prefix() . 'document_types', [
                    'name' => $file->document_type,
                    'description' => 'Migrated from existing files',
                    'is_system' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Update staff_files to populate document_type_id based on existing document_type text
        // Using COLLATE to handle collation differences
        $this->db->query("UPDATE " . db_prefix() . "staff_files sf
            INNER JOIN " . db_prefix() . "document_types dt ON sf.document_type COLLATE utf8mb4_unicode_ci = dt.name COLLATE utf8mb4_unicode_ci
            SET sf.document_type_id = dt.id
            WHERE sf.document_type IS NOT NULL AND sf.document_type != ''");
    }

    public function down()
    {
        // Remove document_type_id column from staff_files
        if ($this->db->field_exists('document_type_id', db_prefix() . 'staff_files')) {
            $this->db->query("ALTER TABLE `" . db_prefix() . "staff_files` DROP COLUMN `document_type_id`");
        }

        // Drop document_types table
        $this->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "document_types`");
    }
}
