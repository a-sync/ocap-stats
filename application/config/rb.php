<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'Red Bear Stats';

$config['site_logo'] = 'public/rb_logo.png';

$config['event_types'] = [
    'tvt' => 'TvT',
    'tvt2' => 'TvT II',
    'if' => 'Iron Front',
    'brutal' => 'Brutal',
    'ltvt' => 'Light TvT'
];

$config['default_selected_event_types'] = [
    'tvt',
    'tvt2'
];

$config['tag_event_types'] = [
    'TvT' => 'tvt',
    'TvT_II' => 'tvt',
    'TvT_Tactical' => 'ltvt',
    'IF' => 'if',
    'Brutal' => 'brutal',
];

// Keep these ordered by rank
$config['cmd_group_names'] = [
    'HQ',
    'Штаб',
    'Alpha 1-1',
    'Alpha 1-2',
    'Альфа 1-1',
    'Альфа 1-2'
];

// Keep these ordered by rank
$config['cmd_role_names'] = [];

$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'https://ocap.red-bear.ru/api/v1/operations/get?type=&name=&newer=2017-06-01&older=2099-12-12&_=1');
define('OPERATION_DATA_JSON_URL_PATH', 'https://ocap.red-bear.ru/data/');
define('OCAP_URL_PREFIX', 'https://ocap.red-bear.ru/?zoom=1.4&x=-150&y=120&file=');
define('ADJUST_HIT_DATA', -1);

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
                    $adj_date_time = new \DateTime($matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5], new \DateTimeZone('Europe/Moscow'));
                    $op['start_time'] = $adj_date_time->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                } catch (exception $e) {
                }
            }
        }

        return $errors;
    }
}
