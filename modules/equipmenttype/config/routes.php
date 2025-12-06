<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('equipmenttype', 'EquipmentType');
$routes->add('equipmenttype/save', 'EquipmentType/save');
$routes->add('equipmenttype/delete/(:num)', 'EquipmentType/delete/$1');