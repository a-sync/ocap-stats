<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = '242 Nightstalkers Stats';

$config['site_logo'] = 'public/242ns_logo.png';

$config['event_types'] = [
    'pve' => 'PvE',
    'ops' => 'Ops'
];

$config['default_selected_event_types'] = ['pve', 'ops'];

$config['tag_event_types'] = [
    'Ops' => 'ops',
    'PvE' => 'pve'
];

// Keep these ordered by rank
$config['cmd_group_names'] = [
    'Alpha 1-1',
    'Alpha 2-1'
];

// Keep these ordered by rank
$config['cmd_role_names'] = [
    'Commander',
    'Platoon Leader',
    'First Platoon Leader'
];

$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'http://server.242nightstalkers.com:5000/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');
define('OPERATION_DATA_JSON_URL_PATH', 'http://server.242nightstalkers.com:5000/data/');
define('OCAP_URL_PREFIX', 'http://server.242nightstalkers.com:5000/?zoom=1.4&x=-100&y=100&file=');

if (!function_exists('preprocess_op_data')) {
    function preprocess_op_data(&$op)
    {
        $errors = [];

        // Some ops are missing the start_time but have a timestamp in the filename
        if (!$op['start_time']) {
            $matches = null;
            // Detect timestamp in filename prefix
            if (preg_match('/^(20[0-9]{2})_([0-9]{2})_([0-9]{2})__([0-9]{2})_([0-9]{2})_/', $op['filename'], $matches)) {
                try {
                    $adj_date_time = new \DateTime($matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5], new \DateTimeZone('America/New_York'));
                    $op['start_time'] = $adj_date_time->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                } catch (exception $e) {
                }
            }
        }

        return $errors;
    }
}
