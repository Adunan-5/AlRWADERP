<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_338 extends App_Migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create holidays table
        if (!$CI->db->table_exists(db_prefix() . 'holidays')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'holidays` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `label` varchar(255) NOT NULL,
              `holiday_date` date NOT NULL,
              `description` text DEFAULT NULL,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              `created_by` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `holiday_date` (`holiday_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'holidays')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'holidays`');
        }
    }
}
