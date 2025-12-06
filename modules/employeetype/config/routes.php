<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('employeetype', 'employeetype');
$routes->add('employeetype/save', 'employeetype/save');
$routes->add('employeetype/delete/(:num)', 'employeetype/delete/$1');