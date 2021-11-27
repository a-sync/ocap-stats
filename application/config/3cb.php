<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = '3CB Stats';

$config['site_logo'] = 'public/3cb_logo.png';

$config['event_types'] = [
    'pve' => 'PvE'
];

$config['default_selected_event_types'] = ['pve'];

$config['tag_event_types'] = [
    'PvE' => 'pve'
];

// Keep these ordered by rank
$config['hq_group_names'] = [
    'Coy',
    '1-0',
    '2-0'
];

// Keep these ordered by rank
$config['hq_role_names'] = [
    'Company Commander',
    'Platoon Commander',
    'Troop Commander',
    'Section Commander'
];

$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'https://ocap.3commandobrigade.com/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');
define('OPERATION_DATA_JSON_URL_PATH', 'https://ocap.3commandobrigade.com/data/');
define('OCAP_URL_PREFIX', 'https://ocap.3commandobrigade.com/?zoom=1.4&x=-150&y=120&file=');

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
                    $adj_date_time = new \DateTime($matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5], new \DateTimeZone('Europe/London'));
                    $op['start_time'] = $adj_date_time->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                } catch (exception $e) {
                }
            }
        }

        return $errors;
    }
}
