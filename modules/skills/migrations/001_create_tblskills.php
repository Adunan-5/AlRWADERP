<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_tblskills extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => '0',
                'collation' => 'utf8mb4_general_ci'
            ],
            'name_arabic' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'collation' => 'utf8mb4_general_ci'
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('id');
        $this->dbforge->create_table('tblskills', TRUE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->dbforge->drop_table('tblskills', TRUE);
    }
}