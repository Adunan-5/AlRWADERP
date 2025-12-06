<?php
defined('BASEPATH') or exit('No direct script access allowed');

$routes->add('ownemployeetype', 'ownemployeetype');
$routes->add('ownemployeetype/save', 'ownemployeetype/save');
$routes->add('ownemployeetype/delete/(:num)', 'ownemployeetype/delete/$1');