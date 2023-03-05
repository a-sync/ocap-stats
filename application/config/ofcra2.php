<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'OFCRA2 Stats';

$config['site_logo'] = 'public/ofcra_logo.png';

$config['event_types'] = [
    'public' => 'Public game',
    'official' => 'Official game',
    'small' => 'Small game'
];

$config['default_selected_event_types'] = ['official', 'public'];

$config['tag_event_types'] = [];//only using TvT :(

// Keep these ordered by rank
$config['cmd_group_names'] = [
    'Headquarter',
    'HQ',
    'Bluelead',
    'Redlead',
    'REDFOR HQ',
    'HQ FR',
    'One',
    '1',
    'Alpha',
    'Alpha 1-1',
    'Zulu',
    'Mike'
];

// Keep these ordered by rank
$config['cmd_role_names'] = [
    'Side leader'
];

$config['sides'] = [
    'WEST' => 'Bluefor',
    'EAST' => 'Redfor',
    'GUER' => 'Greenfor',
    'CIV' => 'Civilian',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'http://game.ofcra.org:5000/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');
define('OPERATION_DATA_JSON_URL_PATH', 'http://game.ofcra.org:5000/data/');
define('OCAP_URL_PREFIX', 'http://game.ofcra.org:5000/?zoom=1.4&x=-100&y=100&file=');

if (!function_exists('should_op_be_ignored')) {
    function should_op_be_ignored($op)
    {
        // Mission lasting less then 20 minutes
        if (floor(intval($op['mission_duration']) / 60) < 20) {
            return true;
        }

        return false;
    }
}
