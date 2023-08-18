<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'OFCRA Stats';

$config['site_logo'] = 'public/ofcra_logo.png';

$config['event_types'] = [
    'official' => 'Official game',
    'public' => 'Public game',
    'small' => 'Small game'
];

$config['default_selected_event_types'] = ['official', 'public', 'small'];

$config['tag_event_types'] = [];

// Keep these ordered by rank
$config['cmd_group_names'] = [
    'Headquarter',
    'HQ',
    'Bluelead',
    'Redlead',
    'One',
    '1',
    'Alpha',
    'Alpha 1-1'
];

// Keep these ordered by rank
$config['cmd_role_names'] = [];

$config['sides'] = [
    'WEST' => 'Bluefor',
    'EAST' => 'Redfor',
    'GUER' => 'Greenfor',
    'CIV' => 'Civilian',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'https://game.ofcra.org/ocap/index.php');
define('OPERATIONS_JSON_URL_CONTENT_REGEX', '/let opList = (\[.*\]);\s*\n/');
define('OPERATION_DATA_JSON_URL_PATH', 'https://game.ofcra.org/ocap/data/');
define('OCAP_URL_PREFIX', 'https://game.ofcra.org/ocap/#');
define('ADJUST_HIT_DATA', -1);

if (!function_exists('should_op_be_ignored')) {
    function should_op_be_ignored($op)
    {
        if (isset($op['can_import']) && !$op['can_import']) {
            return true;
        }

        return false;
    }
}
