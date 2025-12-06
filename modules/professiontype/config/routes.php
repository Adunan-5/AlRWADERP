<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('professiontype', 'profession');
$routes->add('professiontype/save', 'professiontype/save');
$routes->add('professiontype/delete/(:num)', 'professiontype/delete/$1');