<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'FNF Stats';

$config['site_logo'] = 'public/fnf_logo.png';

$config['event_types'] = [
    'eu' => 'FNF EU',
    'na' => 'FNF NA',
    'titans' => 'Titans',
    'tnt' => 'TNT',
    'vsofcra' => 'FNF vs OFCRA',
    'soon' => 'SOON',
    'vssud' => 'FNF vs AS'
];

$config['default_selected_event_types'] = ['eu', 'na'];

$config['tag_event_types'] = [
    'fnfeu' => 'eu',
    'fnfeu1' => 'eu',
    'fnfeu2' => 'eu',
    'fnfna' => 'na',
    'fnfna1' => 'na',
    'fnftitans' => 'titans',
    'fnftnt' => 'tnt',
    'fnfvofcra' => 'vsofcra',
    'SOON' => 'soon',
    'fnfEarly' => 'eu',
    'fnfLate' => 'na'
];

// Keep these ordered by rank
$config['cmd_group_names'] = [
    'CMD',
    'Command',
    'Blufor Command',
    'Opfor Command',
    'Indfor Command',
    'Company HQ',
    'PLHQ',
    'PLTHQ',
    'BLU PLT HQ',
    'OPF PLT HQ',
    'IND PLT HQ',
    'P1HQ',
    'P2HQ',
    'DPTHQ',
    '1st HQ',
    'A',
    'Alpha',
    'A HQ',
    'Blufor A',
    'Opfor A',
    'Indfor A',
    'BLU Alpha HQ',
    'OPF Alpha HQ',
    'IND Alpha HQ',
    'A1',
    'Alpha 1',
    'Blufor A1',
    'Opfor A1',
    'Indfor A1',
    'BLU Alpha 1',
    'OPF Alpha 1',
    'IND Alpha 1',
    'Alpha 1-1'
];

// Keep these ordered by rank
$config['cmd_role_names'] = [
    'Company Commander',
    'Company Sergeant',
    'Platoon Leader',
    'Platoon Sergeant',
    'Squad Leader',
    'Captain',
    'Chief',
    'Evil General',
    'Cult Leader',
    'Osamba Bind Layden'
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
define('OCAP_URL_PREFIX', 'http://aar.fridaynightfight.org/?zoom=1.4&x=-100&y=100&file=');
define('ADJUST_HIT_DATA', 335);
define('OCAP_ODATA_URL', 'https://fnf-odata.devs.space');

if (!function_exists('should_op_be_ignored')) {
    function should_op_be_ignored($op)
    {
        // Warmup or test mission
        if (in_array(strtolower($op['mission_name']), array_map('strtolower', [
            'Friday Night Fight DTAS',
            'temp',
            'FNF_MissionTemplate',
            'FNF King of the Fort',
            'FNF Mission Template'
        ])) || stripos($op['mission_name'], 'FNF_SustainedAssault') === 0) {
            return true;
        }

        // Non titans game lasting less then 20 minutes
        if ($op['tag'] !== 'fnftitans' && floor(intval($op['mission_duration']) / 60) < 20) {
            return true;
        }

        return false;
    }
}

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
                    // Adjust server local time based on tag/event
                    $tz = $op['event'] === 'eu' ? 'Europe/London' : 'America/Goose_Bay'; // na ops timestamp is most likely AST/ADT
                    $adj_date_time = new \DateTime($matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5], new \DateTimeZone($tz));
                    $op['start_time'] = $adj_date_time->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                } catch (exception $e) {
                }
            }
        }

        // If mission_author field is empty, we can try to grab it from the mission_name
        if (!$op['mission_author']) {
            try {
                foreach (['FNF_WWII_', 'NEWORBAT_', 'FNFTitans_', 'FNFWWII_', 'FNFReplay_', 'FNF_', 'TNT2_', 'NO_'] as $prefix) {
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
