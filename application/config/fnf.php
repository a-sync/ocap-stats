<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'FNF Stats';

$config['site_logo'] = 'public/fnf_logo.png';

$config['event_types'] = [
    'eu' => 'FNF EU',
    'na' => 'FNF NA',
    'titans' => 'Titans',
    'tnt' => 'TNT'
];

$config['default_selected_event_types'] = ['eu', 'na'];

$config['tag_event_types'] = [
    'fnfeu' => 'eu',
    'fnfeu1' => 'eu',
    'fnfeu2' => 'eu',
    'fnfna' => 'na',
    'fnftitans' => 'titans',
    'fnftnt' => 'tnt'
];

// Keep these ordered by rank
$config['hq_group_names'] = [
    'CMD',
    'PLTHQ',
    'OPF PLT HQ',
    'IND PLT HQ',
    'P1HQ',
    'P2HQ',
    'DPTHQ',
    'Alpha 1-1'
];

// Keep these ordered by rank
$config['hq_role_names'] = [
    'Company Commander',
    'Platoon Leader',
    'Platoon Leader (HVT)',
    'Osamba Bind Layden',
    'Chief',
    'Cult Leader'
];

$config['ignorable_mission_names'] = [
    'Friday Night Fight DTAS',
    'temp',
    'FNF_MissionTemplate'
];

$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

define('OPERATIONS_JSON_URL', 'http://aar.fridaynightfight.org/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');
define('OPERATION_DATA_JSON_URL_PATH', 'http://aar.fridaynightfight.org/data/');
define('OCAP_URL_PREFIX', 'http://aar.fridaynightfight.org/?zoom=1.4&x=-150&y=120&file=');
define('ADJUST_HIT_DATA', 335);

if (!function_exists('preprocess_op_data')) {
    function preprocess_op_data(&$op)
    {
        $errors = [];

        // Some ops are missing the start_time but have a timestamp in the filename
        if (!$op['start_time']) {
            // Detect timestamp in filename prefix
            if (preg_match('/^20[0-9]{2}_[0-9]{2}_[0-9]{2}__[0-9]{2}_[0-9]{2}_/', $op['filename'])) {
                try {
                    $date_time = str_replace('__', ' ', substr($op['filename'], 0, 17));
                    $date_time_arr = explode(' ', $date_time);
                    $start_date = str_replace('_', '-', $date_time_arr[0]);
                    $start_time = str_replace('_', ':', $date_time_arr[1]);

                    // Adjust server local time based on tag/event
                    $tz = $op['event'] === 'eu' ? 'Europe/London' : 'America/Goose_Bay'; // na ops timestamp is most likely AST/ADT
                    $adj_date_time = new \DateTime($start_date . ' ' . $start_time, new \DateTimeZone($tz));
                    $op['start_time'] = $adj_date_time->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                } catch (exception $e) {
                }
            }
        }

        // If mission_author field is empty, we can try to grab it from the mission_name
        if (!$op['mission_author']) {
            try {
                foreach(['FNF_WWII_', 'NEWORBAT_', 'FNFTitans_', 'FNFWWII_', 'FNFReplay_', 'FNF_', 'TNT2_', 'NO_'] as $prefix) {
                    $mission_name_arr = explode($prefix, $op['mission_name']);
                    if (isset($mission_name_arr[1])) {
                        $op['mission_author'] = explode('_', $mission_name_arr[1], 2)[0];
                        break;
                    }
                }
            } catch (exception $e) {
            }
        }

        return $errors;
    }
}
