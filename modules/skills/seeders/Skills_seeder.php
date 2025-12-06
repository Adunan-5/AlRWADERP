<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Skills_seeder extends CI_Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Communication',
                'name_arabic' => 'التواصل'
            ],
            [
                'name' => 'Leadership',
                'name_arabic' => 'القيادة'
            ],
            [
                'name' => 'Problem Solving',
                'name_arabic' => 'حل المشكلات'
            ]
        ];

        foreach ($data as $skill) {
            $this->db->insert('tblskills', $skill);
        }
    }
}