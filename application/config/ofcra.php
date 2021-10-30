<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'OFCRA Stats';

$config['site_logo'] = 'public/ofcra_logo.png';

$config['event_types'] = [
    'official' => 'Official game',
    'public' => 'Public game',
    'small' => 'Small game',
    'pve' => 'PVE'
];

$config['default_selected_event_types'] = ['official', 'public', 'small', 'pve'];

$config['tag_event_types'] = [];

// Keep these ordered by rank
$config['hq_group_names'] = [
    'Alpha 1-1',
    'Alpha 1-2'
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

define('OPERATIONS_JSON_URL', 'https://game.ofcra.org/ocap/index.php');
define('OPERATIONS_JSON_URL_CONTENT_REGEX', '/let opList = (\[.*\]);\s*\n/');
define('OPERATION_DATA_JSON_URL_PATH', 'https://game.ofcra.org/ocap/data/');
define('OCAP_URL_PREFIX', 'https://game.ofcra.org/ocap/#');
define('FIRST_PVP_OP_WITH_HIT_EVENTS', 999999);
