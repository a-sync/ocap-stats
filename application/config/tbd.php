<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'TBD Stats';

$config['site_logo'] = 'public/tbd_logo.png';

$config['event_types'] = [
    'tvt' => 'TvT'
];

$config['default_selected_event_types'] = ['tvt'];

$config['tag_event_types'] = [
    'TvT' => 'tvt'
];

// Keep these ordered by rank
$config['cmd_group_names'] = [
    'Alpha 1-1'
];

// Keep these ordered by rank
$config['cmd_role_names'] = [
    'Company Commander',
    'Local Commander'
];

$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'http://tbdevent.eu:5000/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');
define('OPERATION_DATA_JSON_URL_PATH', 'http://tbdevent.eu:5000/data/');
define('OCAP_URL_PREFIX', 'http://tbdevent.eu:5000/?zoom=1.4&x=-100&y=100&file=');

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
