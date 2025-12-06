<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('documentworkflow', 'documentworkflow');
$routes->add('documentworkflow/editor', 'documentworkflow/editor');
$routes->add('documentworkflow/editor/(:num)', 'documentworkflow/editor/$1');
$routes->add('documentworkflow/save', 'documentworkflow/save');
$routes->add('documentworkflow/delete/(:num)', 'documentworkflow/delete/$1');
$routes->add('documentworkflow/download/(:num)', 'documentworkflow/download/$1');
$routes->add('documentworkflow/upload_letterhead', 'documentworkflow/upload_letterhead');
$routes->add('documentworkflow/get_document/(:num)', 'documentworkflow/get_document/$1');
$routes->add('documentworkflow/save_type', 'documentworkflow/save_type');
$routes->add('documentworkflow/delete_type/(:num)', 'documentworkflow/delete_type/$1');