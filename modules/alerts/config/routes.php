<?php

defined('BASEPATH') or exit('No direct script access allowed');

$routes->get('alerts', 'alerts/Alerts/index');
$routes->get('alerts/list/(:any)', 'alerts/Alerts/view/$1');
