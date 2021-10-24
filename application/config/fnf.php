<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['event_types'] = [
    'eu' => 'FNF EU',
    'na' => 'FNF NA',
    'titans' => 'Titans',
    'tnt' => 'TNT'
];

$config['event_type_tags'] = [
    'fnfeu' => 'eu',
    'fnfeu1' => 'eu',
    'fnfeu2' => 'eu',
    'fnfna' => 'na',
    'fnftitans' => 'titans',
    'fnftnt' => 'tnt'
];

$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

// Keep these ordered by rank
$config['hq_group_names'] = [
    'CMD',
    'PLTHQ',
    'OPF PLT HQ',
    'IND PLT HQ',
    'P1HQ',
    'P2HQ'
];

// Keep these ordered by rank
$config['hq_role_names'] = [
    'Company Commander',
    'Platoon Leader',
    'Platoon Leader (HVT)',
    'Osamba Bind Layden'
];

define('FNF_OPERATIONS_JSON_URL', 'http://aar.fridaynightfight.org/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');
define('FNF_OPERATION_DATA_JSON_URL_PREFIX', 'http://aar.fridaynightfight.org/data/');
define('FNF_AAR_URL_PREFIX', 'http://aar.fridaynightfight.org/?zoom=1.4&x=-150&y=120&file='); // &frame=0&x=&y=&zoom=9
define('FIRST_OP_WITH_HIT_EVENTS', 335);
