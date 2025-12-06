<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_118 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Drop agreement-related tables as they will be moved to PO/SO modules
        // These tables will be recreated in the Purchase Orders and Sales Orders modules
        // where equipment details, pricing tiers, and charges actually belong

        // Drop pricing tiers table first (has FK to agreement_items)
        if ($CI->db->table_exists(db_prefix() . 'equipment_agreement_pricing_tiers')) {
            $CI->db->query('DROP TABLE ' . db_prefix() . 'equipment_agreement_pricing_tiers');
        }

        // Drop charges table (has FK to agreements)
        if ($CI->db->table_exists(db_prefix() . 'equipment_agreement_charges')) {
            $CI->db->query('DROP TABLE ' . db_prefix() . 'equipment_agreement_charges');
        }

        // Drop agreement items table (has FK to agreements and equipments)
        if ($CI->db->table_exists(db_prefix() . 'equipment_agreement_items')) {
            $CI->db->query('DROP TABLE ' . db_prefix() . 'equipment_agreement_items');
        }

        // Note: We keep tblequipment_agreements as it's the master framework contract
        // Equipment details, pricing, and charges will be managed in:
        // - Quotations module (sales to clients)
        // - Sales Orders module (confirmed sales)
        // - Purchase Orders module (procurement from suppliers)
    }

    public function down()
    {
        // Cannot automatically recreate these tables
        // If needed, run migrations 114, 115, 116 manually
    }
}
