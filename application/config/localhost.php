<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['admin_key'] = 'team';

$config['site_title'] = 'OCAP Stats';

$config['site_logo'] = 'public/ocap_logo.png';

/*
|--------------------------------------------------------------------------
| Event types
|--------------------------------------------------------------------------
|
| The ID and name pairs of available event types to categorize the 
| operations by.
|
*/
$config['event_types'] = [
    'pve' => 'PvE',
    'pvp' => 'PvP'
];

/*
|--------------------------------------------------------------------------
| Event types selected by default
|--------------------------------------------------------------------------
|
| List of IDs of the event types selected on the front end by default.
|
*/
$config['default_selected_event_types'] = ['pve', 'pvp'];

/*
|--------------------------------------------------------------------------
| Tags to event type designations
|--------------------------------------------------------------------------
|
| Operaton tag and event type ID pairs. Operations can only be processed 
| as the designated events.
|
| NOTE: If left empty, the operation event type must be selected 
|       manually before processing.
*/
$config['tag_event_types'] = [];

/*
|--------------------------------------------------------------------------
| CMD group names
|--------------------------------------------------------------------------
|
| List of group names to select the commanders from.  
| (Needs to be an exact match.)
|
| IMPORTANT: Keep these ordered by rank!
*/
$config['cmd_group_names'] = [
    'Alpha 1-1',
    'Alpha 2-1'
];

/*
|--------------------------------------------------------------------------
| CMD role names
|--------------------------------------------------------------------------
|
| List of role names to consider when selecting the commanders.  
| (Only needs to match the beginning of the role.)
|
| IMPORTANT: Keep these ordered by rank!
*/
$config['cmd_role_names'] = [];

/*
|--------------------------------------------------------------------------
| Side names
|--------------------------------------------------------------------------
|
| Arma 3 side and name pairs.
|
*/
$config['sides'] = [
    'WEST' => 'BLUFOR',
    'EAST' => 'OPFOR',
    'GUER' => 'IND',
    'CIV' => 'CIV',
    '' => '',
    'UNKNOWN' => 'unknown'
];

/*
|--------------------------------------------------------------------------
| operations.json URL
|--------------------------------------------------------------------------
|
| The URL that serves the list of operations in JSON format.
|
*/
define('OPERATIONS_JSON_URL', 'http://localhost:5000/api/v1/operations?tag=&name=&newer=2017-06-01&older=2099-12-12');

/*
|--------------------------------------------------------------------------
| operations.json URL content regex
|--------------------------------------------------------------------------
|
| If the operations.json URL serves anything other then a JSON file, you 
| can define a regex to extract the actual JSON string.
|
*/
// define('OPERATIONS_JSON_URL_CONTENT_REGEX', '/let opList = (\[.*\]);\s*\n/');

/*
|--------------------------------------------------------------------------
| Operation data JSON URL path
|--------------------------------------------------------------------------
|
| The URL prefix for the individual operation data JSON files.
|
*/
define('OPERATION_DATA_JSON_URL_PATH', 'http://localhost:5000/data/');

/*
|--------------------------------------------------------------------------
| OCAP URL prefix
|--------------------------------------------------------------------------
|
| A URL pointing to the OCAP interface. The operation filename is appended
| at the end.
|
*/
define('OCAP_URL_PREFIX', 'http://localhost:5000/?zoom=1.4&x=-100&y=100&file=');

/*
|--------------------------------------------------------------------------
| Adjust hit event data
|--------------------------------------------------------------------------
|
| Operations recorded before using OCAP2 v1.1.0 had no proper tracking for 
| hit events where the victim is a player. Hit stats must be adjusted to 
| account for this.  
| Set the value to the ID of the first operation where OCAP2 v1.1.0 or 
| later was used. You can ignore this setting if only PvE operations are 
| affected.
|
| Options are:
|
|   undefined: No adjustment needed  
|       == -1: Hide all hit data  
|       >=  0: Only calculate shots/hits for operations starting from the 
|              operation ID defined here  
|
*/
// define('ADJUST_HIT_DATA', 0);

/*
|--------------------------------------------------------------------------
| Should operation be ignored
|--------------------------------------------------------------------------
|
| Helper function to check an operation's available data and recommend 
| whether or not to ignore this operation.
|
*/
/*
if (!function_exists('should_op_be_ignored')) {
    // @param $op array of operation data fields
    // @return boolean recommendation to ignore the operation or not
    function should_op_be_ignored($op)
    {
        return false;
    }
}
*/

/*
|--------------------------------------------------------------------------
| Preprocess operation data
|--------------------------------------------------------------------------
|
| Helper function to modify a processed operation's data fields before 
| it is stored in the database.
|
*/
/*
if (!function_exists('preprocess_op_data')) {
    // @param $op array of operation data fields passed by reference
    // @return string array of non fatal error messages
    function preprocess_op_data(&$op)
    {
        $errors = [];

        return $errors;
    }
}
*/

/*
|--------------------------------------------------------------------------
| Manage the data JSON files manually
|--------------------------------------------------------------------------
|
| Require the admin to download / delete the operation data JSON files.  
| Useful during development.
|
*/
// define('MANAGE_DATA_JSON_FILES', true);
