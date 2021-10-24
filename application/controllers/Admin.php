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
            $status = download_file(FNF_OPERATIONS_JSON_URL, './json/operations.json');
            if ($status !== 200) {
                log_message('error', 'Update operations.json returned status code: ' . $status);
            }
        }

        return redirect(base_url('manage'));
    }

    // operations
    public function index()
    {
        require_once(APPPATH . 'third_party/json-machine/load.php');

        $this->load->helper(['cache', 'date']);
        $this->load->model('operations');

        $file_size = '0 B';
        $last_update = 0;
        $last_cache_update = 0;
        $operations = [];
        $op_db_ids = $this->operations->get_all_ids_and_events();

        if (file_exists('./json/operations.json')) {
            $file_size = $this->_convert_filesize(filesize('./json/operations.json'));
            $last_update = filemtime('./json/operations.json');
            $operations = \JsonMachine\JsonMachine::fromFile('./json/operations.json');
        }

        $operations_rev = [];
        foreach ($operations as $op) {
            array_unshift($operations_rev, $op);
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

        $this->load->helper(['curl', 'array']);
        $this->load->model('operations');

        $errors = [];
        $operation = null;
        $op_in_db = false;
        $op = false;

        if (filter_var($op_id, FILTER_VALIDATE_INT)) {
            $operations = \JsonMachine\JsonMachine::fromFile('./json/operations.json');
            foreach ($operations as $o) {
                if (intval($op_id) === $o['id']) {
                    $operation = $o;
                    break;
                }
            }

            if ($operation) {
                $op_in_db = $this->operations->exists($operation['id']);

                $event_type_tags = $this->config->item('event_type_tags');
                $event_type_match = isset($event_type_tags[$operation['tag']]) && !in_array($operation['mission_name'], ['Friday Night Fight DTAS', 'temp', 'FNF_MissionTemplate']) ? $event_type_tags[$operation['tag']] : '';

                // ID was posted
                if ($operation['id'] === intval($this->input->post('id'))) {
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
                                    if ($event_type_match === $event_type) {
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
                                    $status = download_file(FNF_OPERATION_DATA_JSON_URL_PREFIX . $operation['filename'], './json/' . $operation['filename']);
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
                    $operation['last_update'] = date('Y-m-d H:i:s', filemtime('./json/' . $operation['filename']));
                }

                if ($op_in_db) {
                    $op = $this->operations->get_by_id($operation['id']);
                }
            } else {
                $errors[] = 'Unknown operation ID given.';
            }
        } else {
            $errors[] = 'Invalid ID given.';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        $this->_head('manage', $operation ? $operation['mission_name'] . ' (' . $operation['id'] . ')' : '');

        $this->load->view('admin/process', [
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
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor(log($bytes, 1024));
        return sprintf('%.' . $decimals . 'f', $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }

    private function _parse_operation_json($operation)
    {
        $markers = $this->operations->parse_markers(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/Markers'));

        try {
            $operation['addon_version'] = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/addonVersion'))[0];
        } catch (exception $e) {
            $operation['addon_version'] = '';
        }

        try {
            $operation['capture_delay'] = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/captureDelay'))[0];
        } catch (exception $e) {
            $operation['capture_delay'] = '';
        }

        try {
            $operation['extension_build'] = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/extensionBuild'))[0];
        } catch (exception $e) {
            $operation['extension_build'] = '';
        }

        try {
            $operation['extension_version'] = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/extensionVersion'))[0];
        } catch (exception $e) {
            $operation['extension_version'] = '';
        }

        try {
            $operation['mission_author'] = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/missionAuthor'))[0];
        } catch (exception $e) {
            $operation['mission_author'] = explode('_', $operation['mission_name'])[1];
        }

        try {
            $operation['start_time'] = iterator_to_array(\JsonMachine\JsonMachine::fromFile('./json/' . $operation['filename'], '/times'))[0]['systemTimeUTC'];
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
            'title' => ($prefix ? $prefix . ' - ' : '') . 'FNF Stats Admin' . ($postfix ? ' - ' . $postfix : ''),
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
        // $hq_unsolvable = $this->players->get_commanders(false, true);
        // $no_winner = $this->operations->get_ops(false, false, true);

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
