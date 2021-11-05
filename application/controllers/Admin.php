<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    private $adminUser = false;

    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('X-Powered-By: 💖');

        $this->load->library('session');

        if (!$this->session->admin) {
            return redirect(base_url('login'));
        }
        $this->adminUser = $this->session->admin;
        if ($this->router->method !== 'logout') {
            session_write_close();
        }
    }

    public function logout()
    {
        $this->load->library('session');

        $this->session->sess_destroy();
        session_write_close();

        log_message('error', 'EVENT --> Admin [' . $this->adminUser['name'] . '] logging out @ ' . $this->input->ip_address());
        $this->adminUser = false;

        return redirect(base_url(''));
    }

    public function clearcache()
    {
        $this->load->helper(['file', 'cache']);

        $clear = $this->input->post('clear_cache');
        if ($clear) {
            delete_all_cache();

            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] cleared site cache');
        }

        return redirect(base_url('manage'));
    }

    public function update()
    {
        $this->load->helper('curl');

        $update = $this->input->post('update_operations');

        if ($update) {
            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] updated operations.json');
            $status = download_file(OPERATIONS_JSON_URL, './json/operations.json.tmp');
            if ($status !== 200) {
                log_message('error', 'operations.json update returned status code: ' . $status);
            }

            if (defined('OPERATIONS_JSON_URL_CONTENT_REGEX')) {
                $matches = [];
                if (preg_match(OPERATIONS_JSON_URL_CONTENT_REGEX, file_get_contents('./json/operations.json.tmp'), $matches)) {
                    if (!file_put_contents('./json/operations.json.tmp', $matches[1], LOCK_EX)) {
                        log_message('error', 'operations.json.tmp write failed');
                        unlink('./json/operations.json.tmp');
                    }
                } else {
                    log_message('error', 'operations.json.tmp regex failed');
                    unlink('./json/operations.json.tmp');
                }
            }

            if (file_exists('./json/operations.json.tmp')) {
                rename('./json/operations.json.tmp', './json/operations.json');
            }
        }

        return redirect(base_url('manage'));
    }

    public function index()
    {
        $this->operations();
    }

    // manage
    public function operations()
    {
        require_once(APPPATH . 'third_party/json-machine/load.php');

        $this->load->helper(['cache', 'date']);
        $this->load->model('operations');

        $file_size = '0 B';
        $last_update = 0;
        $last_cache_update = 0;
        $operations = [];
        $operations_rev = [];
        $op_db_ids = $this->operations->get_all_ids_and_events();

        if (file_exists('./json/operations.json')) {
            $last_update = filemtime('./json/operations.json');
            $file_size_bytes = filesize('./json/operations.json');

            if ($file_size_bytes > 0) {
                $file_size = $this->_convert_filesize($file_size_bytes);

                $operations = \JsonMachine\JsonMachine::fromFile('./json/operations.json');

                foreach ($operations as $op) {
                    $op['id'] = intval($op['id']);
                    if (!isset($op['tag']) && isset($op['type'])) {
                        $op['tag'] = $op['type'];
                    }
                    array_unshift($operations_rev, $op);
                }
            }
        }

        $homepage_cache_file = get_cache_file('');
        if (file_exists($homepage_cache_file)) {
            $last_cache_update = filemtime($homepage_cache_file);
        }

        $this->_head();

        $this->load->view('admin/operations', [
            'operations' => $operations_rev,
            'file_size' => $file_size,
            'last_update' => $last_update,
            'last_cache_update' => $last_cache_update,
            'op_db_ids' => $op_db_ids
        ]);

        $this->_foot();
    }

    public function manage($op_id = null)
    {
        require_once(APPPATH . 'third_party/json-machine/load.php');

        $this->load->helper(['curl']);
        $this->load->model('operations');

        $errors = [];
        $operation = null;
        $op_in_db = false;
        $event_type_match = true;
        $op = false;

        if (file_exists('./json/operations.json')) {
            if (filter_var($op_id, FILTER_VALIDATE_INT) || filter_var($op_id, FILTER_VALIDATE_INT) === 0) {
                $operations = \JsonMachine\JsonMachine::fromFile('./json/operations.json');
                foreach ($operations as $o) {
                    $o['id'] = intval($o['id']);
                    if (!isset($o['tag']) && isset($o['type'])) {
                        $o['tag'] = $o['type'];
                    }
                    if (intval($op_id) === $o['id']) {
                        $operation = $o;
                        break;
                    }
                }

                if ($operation) {
                    $op_in_db = $this->operations->exists($operation['id']);

                    if (in_array($operation['mission_name'], $this->config->item('ignorable_mission_names'))) {
                        $event_type_match = false;
                    } elseif (isset($operation['tag'])) {
                        $tag_event_types = $this->config->item('tag_event_types');
                        if (count($tag_event_types) > 0) {
                            $event_type_match = isset($tag_event_types[$operation['tag']]) ? $tag_event_types[$operation['tag']] : false;
                        }
                    }

                    // ID was posted
                    if (!is_null($this->input->post('id')) && $operation['id'] === intval($this->input->post('id'))) {
                        $action = $this->input->post('action');

                        log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] operation (' . $operation['id'] . ') action: ' . $action);
                        // Note: these actions require a file lock
                        if (in_array($action, ['ignore', 'parse', 'update', 'del'])) {
                            $_lock = fopen('./json/op_' . $operation['id'] . '.lock', 'w');
                            if (flock($_lock, LOCK_EX | LOCK_NB)) { // Lock
                                set_time_limit(0);
                                ignore_user_abort(true);

                                try {
                                    if ($op_in_db === false && $action === 'ignore') {
                                        if (!$this->operations->insert($operation)) {
                                            $errors[] = 'Failed to save operation.';
                                        }

                                        if (count($errors) === 0) {
                                            return redirect(base_url('manage/#id-' . $operation['id']));
                                        }
                                        $op_in_db = $this->operations->exists($operation['id']);
                                    } elseif ($op_in_db === false && $action === 'parse') {
                                        $event_type = $this->input->post('event');
                                        if ($event_type_match === true || $event_type_match === $event_type) {
                                            if (in_array($event_type, array_keys($this->config->item('event_types')))) {
                                                $operation['event'] = $event_type;

                                                if (file_exists('./json/' . $operation['filename'])) {
                                                    $opdata = $this->_parse_operation_json($operation);
                                                    $errors = $this->operations->process_op_data($opdata['details'], $opdata['entities'], $opdata['events'], $opdata['markers']);
                                                    $opdata = null;

                                                    if (count($errors) === 0) {
                                                        return redirect(base_url('manage/#id-' . $operation['id']));
                                                    }
                                                    $op_in_db = $this->operations->exists($operation['id']);
                                                } else {
                                                    $errors[] = 'Download the data file first!';
                                                }
                                            } else {
                                                $errors[] = 'You must select an event type!';
                                            }
                                        } else {
                                            $errors[] = 'Unsupported event type for this mission!';
                                        }
                                    } elseif ($action === 'update') {
                                        $status = download_file(OPERATION_DATA_JSON_URL_PATH . $operation['filename'], './json/' . $operation['filename']);
                                        if ($status !== 200) {
                                            $errors[] = 'Update/download operation (' . $operation['id'] . ') returned status code: ' . $status;
                                        }
                                    } elseif ($action === 'del') {
                                        if (file_exists('./json/' . $operation['filename'])) {
                                            unlink('./json/' . $operation['filename']);
                                        }
                                    } else {
                                        $errors[] = 'Invalid action.';
                                    }
                                } catch (Exception $e) {
                                    $errors[] = $e->getMessage();
                                } finally {
                                    flock($_lock, LOCK_UN); // Unlock
                                }
                            } else {
                                $errors[] = 'Another action is in progress!';
                            }
                            fclose($_lock);
                        } elseif ($op_in_db === true && $action === 'purge') {
                            $err = $this->operations->purge($operation['id']);
                            if ($err === false) {
                                $op_in_db = false;
                            } else {
                                $errors = $err;
                                $op_in_db = $this->operations->exists($operation['id']);
                            }
                        } else {
                            $errors[] = 'Invalid action.';
                        }
                    }

                    $operation['filesize'] = '0 B';
                    $operation['last_update'] = 'none';
                    if (file_exists('./json/' . $operation['filename'])) {
                        $operation['filesize'] = $this->_convert_filesize(filesize('./json/' . $operation['filename']));
                        $operation['last_update'] = gmdate('Y-m-d H:i:s', filemtime('./json/' . $operation['filename']));
                    }

                    if ($op_in_db) {
                        $op = $this->operations->get_by_id($operation['id'], false);
                    }
                } else {
                    $errors[] = 'Unknown operation ID given.';
                }
            } else {
                $errors[] = 'Invalid ID given.';
            }
        } else {
            $errors[] = 'operations.json is missing!';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        $this->_head('manage', $operation ? $operation['mission_name'] . ' (' . $operation['id'] . ')' : '');

        $this->load->view('admin/manage', [
            'operation' => $operation,
            'errors' => $errors,
            'op_in_db' => $op_in_db,
            'event_type_match' => $event_type_match,
            'op' => $op
        ]);

        $this->_foot();
    }

    private function _convert_filesize($bytes, $decimals = 2)
    {
        if (intval($bytes) === 0) {
            return '0 B';
        }
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor(log($bytes, 1024));
        return sprintf('%.' . $decimals . 'f', $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }

    private function _parse_operation_json($operation)
    {
        if (!isset($operation['tag'])) {
            $operation['tag'] = '';
        }

        try {
            $markers = $this->operations->parse_markers(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/Markers'));
        } catch (exception $e) {
            $markers = [];
        }

        try {
            $addon_version = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/addonVersion'));
            $operation['addon_version'] = isset($addon_version[0]) ? $addon_version[0] : '';
        } catch (exception $e) {
            $operation['addon_version'] = '';
        }

        try {
            $capture_delay = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/captureDelay'));
            $operation['capture_delay'] = isset($capture_delay[0]) ? $capture_delay[0] : '';
        } catch (exception $e) {
            $operation['capture_delay'] = '';
        }

        try {
            $extension_build = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/extensionBuild'));
            $operation['extension_build'] = isset($extension_build[0]) ? $extension_build[0] : '';
        } catch (exception $e) {
            $operation['extension_build'] = '';
        }

        try {
            $extension_version = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/extensionVersion'));
            $operation['extension_version'] = isset($extension_version[0]) ? $extension_version[0] : '';
        } catch (exception $e) {
            $operation['extension_version'] = '';
        }

        try {
            $mission_author = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/missionAuthor'));
            $operation['mission_author'] = isset($mission_author[0]) ? $mission_author[0] : '';
        } catch (exception $e) {
            $operation['mission_author'] = '';
        }

        try {
            $start_time = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/times'));
            $operation['start_time'] = isset($start_time[0]) ? $start_time[0]['systemTimeUTC'] : '';
        } catch (exception $e) {
            $operation['start_time'] = '';
        }

        $entities = $this->operations->parse_entities(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/entities'));
        $events = $this->operations->parse_events(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/events'));

        $operation['end_winner'] = $events['end_winner'];
        $operation['end_message'] = $events['end_message'];

        return array(
            'markers' => $markers,
            'details' => $operation,
            'entities' => $entities['entities'],
            'events' => array_merge($events['events'], $entities['events'])
        );
    }

    private function _head($active = 'manage', $prefix = '', $postfix = '')
    {
        return $this->load->view('head', [
            'title' => ($prefix ? $prefix . ' - ' : '') . $this->config->item('site_title') . ' Admin' . ($postfix ? ' - ' . $postfix : ''),
            'main_menu' => $this->load->view('admin/menu', ['active' => $active], true)
        ]);
    }

    private function _foot($active = 'admin')
    {
        return $this->load->view('foot', [
            'active' => $active
        ]);
    }

    public function addalias()
    {
        $this->load->model('players');
        $players = $this->players->get_players_names();

        $errors = ['💩 TODO'];
        $this->_head('addalias', 'Add player alias');

        $this->load->view('admin/addalias', [
            'players' => $players,
            'errors' => $errors
        ]);

        $this->_foot();
    }

    public function fixopdata()
    {
        // $this->load->model('players');
        // $this->load->model('operations');
        // $hq_unsolvable = $this->players->get_commanders(true, true);
        // $no_winner = $this->operations->get_ops(true, false, true);

        $errors = ['💩 TODO'];
        $this->_head('fixopdata', 'Fill in missing op data');

        $this->load->view('admin/fixopdata', [
            // 'ops' => $ops,
            // 'op_units' => $op_units,
            'errors' => $errors
        ]);

        $this->_foot();
    }
}
