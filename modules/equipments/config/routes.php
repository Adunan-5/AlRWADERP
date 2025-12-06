<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('equipments', 'equipments');
$routes->add('equipments/add', 'supplequipmentsiers/add');
$routes->add('equipments/edit/(:num)', 'equipments/edit/$1');
$routes->add('equipments/delete/(:num)', 'equipments/delete/$1');
