<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('skills', 'skills');
$routes->add('skills/save', 'skills/save');
$routes->add('skills/delete/(:num)', 'skills/delete/$1');