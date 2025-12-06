<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('suppliers', 'suppliers');
$routes->add('suppliers/add', 'suppliers/add');
$routes->add('suppliers/edit/(:num)', 'suppliers/edit/$1');
$routes->add('suppliers/delete/(:num)', 'suppliers/delete/$1');
