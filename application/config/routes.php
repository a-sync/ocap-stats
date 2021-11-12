<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes with
| underscores in the controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'app/ops';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'app/login';
$route['login/(:any)/(:any)'] = 'app/zerosec/$1/$2';
$route['logout'] = 'admin/logout';

$route['clearcache'] = 'admin/clearcache';
$route['update'] = 'admin/update';
$route['manage'] = 'admin/operations';
$route['manage/(:num)'] = 'admin/manage/$1';
$route['add-alias'] = 'data/add_alias';
$route['fix-op-data'] = 'data/fix_op_data';

$route['players'] = 'app/players';
$route['player/(:num)'] = 'app/player/$1';
$route['player/(:num)/ops'] = 'app/player/$1/ops';
$route['player/(:num)/roles'] = 'app/player/$1/roles';
$route['player/(:num)/weapons'] = 'app/player/$1/weapons';
$route['player/(:num)/attackers'] = 'app/player/$1/attackers';
$route['player/(:num)/victims'] = 'app/player/$1/victims';

$route['ops'] = 'app/ops';
$route['op/(:num)'] = 'app/op/$1';
$route['op/(:num)/entities'] = 'app/op/$1/entities';
$route['op/(:num)/events'] = 'app/op/$1/events';
$route['op/(:num)/weapons'] = 'app/op/$1/weapons';

$route['commanders'] = 'app/commanders';

$route['assorted-data'] = 'app/assorted_data';
$route['about'] = 'app/readme_md';
