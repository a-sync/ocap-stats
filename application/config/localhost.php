<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'OCAP Stats';

$config['site_logo'] = 'public/ocap_logo.png';

$config['event_types'] = [
    'pve' => 'PVE',
    'pvp' => 'PVP'
];

$config['default_selected_event_types'] = ['pve', 'pvp'];

$config['tag_event_types'] = [];

// Keep these ordered by rank
$config['hq_group_names'] = [
    'Alpha 1-1',
    'Alpha 2-1'
];

// Keep these ordered by rank
$config['hq_role_names'] = [];

$config['ignorable_mission_names'] = [];

$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'http://localhost:5000/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');
define('OPERATION_DATA_JSON_URL_PATH', 'http://localhost:5000/data/');
define('OCAP_URL_PREFIX', 'http://localhost:5000/?zoom=1.4&x=-150&y=120&file='); // &frame=0&x=&y=&zoom=9
define('FIRST_PVP_OP_WITH_HIT_EVENTS', 0);
