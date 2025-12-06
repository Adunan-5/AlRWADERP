<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('companytype', 'companytype');
$routes->add('companytype/save', 'companytype/save');
$routes->add('companytype/delete/(:num)', 'companytype/delete/$1');