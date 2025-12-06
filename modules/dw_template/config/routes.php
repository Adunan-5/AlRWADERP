<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('dw_template', 'dw_template');
$routes->add('dw_template/editor', 'dw_template/editor');
$routes->add('dw_template/editor/(:num)', 'dw_template/editor/$1');
$routes->add('dw_template/save', 'dw_template/save');
$routes->add('dw_template/delete/(:num)', 'dw_template/delete/$1');
$routes->add('dw_template/download/(:num)', 'dw_template/download/$1');
$routes->add('dw_template/upload_letterhead', 'dw_template/upload_letterhead');
$routes->add('dw_template/get_document/(:num)', 'dw_template/get_document/$1');
$routes->add('dw_template/get_type_template/(:num)', 'dw_template/get_type_template/$1');
$routes->add('dw_template/save_type', 'dw_template/save_type');
$routes->add('dw_template/delete_type/(:num)', 'dw_template/delete_type/$1');