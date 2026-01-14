<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    private $adminUser = false;
    private $_json_decoder = null;

    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('X-Powered-By: 🐹🐹');
        // $this->output->enable_profiler(TRUE); // DEBUG

        $this->load->library('session');

        if (!$this->session->admin) {
            return redirect(base_url('login'));
        }

        $this->adminUser = $this->session->admin;
        if ($this->router->method !== 'logout') {
            session_write_close();
        }

        define('JSONPATH', APPPATH . 'cache/json/');
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

    private function _head($active = 'manage', $prefix = '', $postfix = '')
    {
        return $this->load->view('head', [
            'title' => ($prefix ? $prefix . ' - ' : '') . $this->config->item('site_title') . ' Admin' . ($postfix ? ' - ' . $postfix : ''),
            'main_menu' => $this->load->view('admin/menu', ['active' => $active], true)
        ]);
    }

    private function _foot()
    {
        return $this->load->view('foot', ['year' => false, 'years' => []]);
    }

    public function clearcache()
    {
        $this->load->helper(['file', 'cache']);

        $clear = $this->input->post('clear_cache');
        if ($clear) {
            delete_all_cache();

            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] cleared site cache');
        }

        $re = $this->input->post('redirect');
        if (!in_array($re, ['add-alias', 'fix-data', 'fix-data/unverified'])) {
            $re = 'manage';
        }

        return redirect(base_url($re));
    }

    public function update()
    {
        $this->load->helper(['curl', 'file']);

        if (!is_dir(JSONPATH)) {
            if (!mkdir(JSONPATH, 0700, TRUE)) {
                log_message('error', "JSON save path '" . JSONPATH . "' is not a directory, doesn't exist or cannot be created.");
                return redirect(base_url('manage#json-dir-error'));
            }
        } elseif (!is_writable(JSONPATH)) {
            log_message('error', "JSON save path '" . JSONPATH . "' is not writable by the PHP process.");
            return redirect(base_url('manage#json-dir-writable-error'));
        }

        $update = $this->input->post('update_operations');

        if ($update) {
            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] updated operations.json');
            if (!download_file(OPERATIONS_JSON_URL, JSONPATH . 'operations.json.tmp')) {
                log_message('error', 'operations.json download failed.');
            } else {
                if (defined('OPERATIONS_JSON_URL_CONTENT_REGEX')) {
                    $matches = [];
                    if (preg_match(OPERATIONS_JSON_URL_CONTENT_REGEX, file_get_contents(JSONPATH . 'operations.json.tmp'), $matches)) {
                        if (!file_put_contents(JSONPATH . 'operations.json.tmp', $matches[1], LOCK_EX)) {
                            log_message('error', 'operations.json.tmp write failed.');
                            unlink(JSONPATH . 'operations.json.tmp');
                        }
                    } else {
                        log_message('error', 'operations.json.tmp regex failed.');
                        unlink(JSONPATH . 'operations.json.tmp');
                    }
                }

                if (file_exists(JSONPATH . 'operations.json.tmp')) {
                    rename(JSONPATH . 'operations.json.tmp', JSONPATH . 'operations.json');
                }
            }
        }

        return redirect(base_url('manage'));
    }

    public function index()
    {
        $this->operations();
    }

    public function operations()
    {
        $this->load->helper('cache');
        $this->load->model('operations');

        $file_size = 0;
        $last_update = 0;
        $last_cache_update = 0;
        $operations = [];
        $operations_rev = [];
        $ops_in_db = $this->operations->get_all_ops();

        if (file_exists(JSONPATH . 'operations.json')) {
            $last_update = filemtime(JSONPATH . 'operations.json');
            $file_size = filesize(JSONPATH . 'operations.json');

            if (intval($file_size) > 0) {
                $operations = $this->_parse_json(JSONPATH . 'operations.json');

                foreach ($operations as $o) {
                    $o['id'] = intval($o['id']);
                    if (!isset($o['tag']) && isset($o['type'])) {
                        $o['tag'] = $o['type'];
                    }
                    array_unshift($operations_rev, $o);
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
            'ops_in_db' => $ops_in_db
        ]);

        $this->_foot();
    }

    public function process($op_id = null)
    {
        $this->load->helper('curl');
        $this->load->model('operations');

        $errors = [];
        $operation = null;
        $op_in_db = false;
        $valid_event_types = [];
        $should_ignore = false;
        $op = false;
        $file_size = 0;
        $last_update = 'none';
        $action = null;
        $redirect = null;
        $is_ajax_req = false;

        if (file_exists(JSONPATH . 'operations.json')) {
            if (filter_var($op_id, FILTER_VALIDATE_INT) || filter_var($op_id, FILTER_VALIDATE_INT) === 0) {

                $operations = $this->_parse_json(JSONPATH . 'operations.json');
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
                    $valid_event_types = get_valid_event_types($operation);
                    $should_ignore = should_ignore($operation);

                    // ID was posted
                    if (!is_null($this->input->post('id')) && $operation['id'] === intval($this->input->post('id'))) {
                        $action = $this->input->post('action');
                        $redirect = $this->input->post('redirect');
                        $is_ajax_req = boolval($this->input->post('ajax'));

                        log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] operation (' . $operation['id'] . ') action: ' . $action);

                        if (in_array($action, ['purge', 'ignore', 'parse', 'update', 'del'])) {
                            $_lock = fopen(JSONPATH . 'op_' . $operation['id'] . '.lock', 'w');
                            if (flock($_lock, LOCK_EX | LOCK_NB)) { // Lock
                                set_time_limit(0);
                                ignore_user_abort(true);

                                try {
                                    if ($action === 'purge' && $op_in_db === true) {
                                        $err = $this->operations->purge($operation['id']);
                                        if ($err === false) {
                                            $op_in_db = false;
                                        } else {
                                            $errors = array_merge($errors, $err);
                                            $op_in_db = $this->operations->exists($operation['id']);
                                        }

                                        if (!defined('MANAGE_DATA_JSON_FILES')) {
                                            $this->_json_del($operation);
                                        }
                                    } elseif ($action === 'ignore' && $op_in_db === false) {
                                        if (!$this->operations->insert($operation)) {
                                            $errors[] = 'Failed to save operation.';
                                        }

                                        if (count($errors) === 0 && $redirect === 'list' && !$is_ajax_req) {
                                            return redirect(base_url('manage/#id-' . $operation['id']));
                                        }
                                        $op_in_db = $this->operations->exists($operation['id']);

                                        if (!defined('MANAGE_DATA_JSON_FILES')) {
                                            $this->_json_del($operation);
                                        }
                                    } elseif ($action === 'parse' && $op_in_db === false && count($valid_event_types) > 0) {
                                        $event_type = $this->input->post('event');
                                        if (in_array($event_type, $valid_event_types)) {
                                            $operation['event'] = $event_type;

                                            if (!file_exists(JSONPATH . $operation['filename']) && !defined('MANAGE_DATA_JSON_FILES')) {
                                                $err = $this->_json_update($operation);
                                                $errors = array_merge($errors, $err);
                                            }

                                            if (file_exists(JSONPATH . $operation['filename'])) {
                                                $opdata = $this->_parse_operation_json($operation);
                                                $err = $this->operations->process_op_data($opdata['details'], $opdata['entities'], $opdata['events'], $opdata['markers'], $opdata['times']);
                                                $opdata = null;

                                                if (count($err) === 0 && !defined('MANAGE_DATA_JSON_FILES')) {
                                                    $this->_json_del($operation);
                                                }
                                                $errors = array_merge($errors, $err);
                                                log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] operation (' . $operation['id'] . ') parsing finished (errors: ' . count($errors) . ')');

                                                if (count($errors) === 0 && $redirect === 'list' && !$is_ajax_req) {
                                                    return redirect(base_url('manage/#id-' . $operation['id']));
                                                }
                                                $op_in_db = $this->operations->exists($operation['id']);
                                            } else {
                                                if (defined('MANAGE_DATA_JSON_FILES')) {
                                                    $errors[] = 'Download the data file first!';
                                                } else {
                                                    $errors[] = 'Data file download failed.';
                                                }
                                            }
                                        } else {
                                            $errors[] = 'Invalid event type for this operation!';
                                        }
                                    } elseif ($action === 'update' && defined('MANAGE_DATA_JSON_FILES')) {
                                        $err = $this->_json_update($operation);
                                        $errors = array_merge($errors, $err);
                                    } elseif ($action === 'del' && defined('MANAGE_DATA_JSON_FILES')) {
                                        $this->_json_del($operation);
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
                        } else {
                            $errors[] = 'Unknown action.';
                        }
                    }

                    if (file_exists(JSONPATH . $operation['filename'])) {
                        $file_size = filesize(JSONPATH . $operation['filename']);
                        $last_update = gmdate('Y-m-d H:i:s', filemtime(JSONPATH . $operation['filename']));
                    }

                    if ($op_in_db) {
                        $op = $this->operations->get_by_id($operation['id'], false);
                    }
                } else {
                    $errors[] = 'Unknown operation ID given.';
                }
            } else {
                $errors[] = 'Invalid operation ID given.';
            }
        } else {
            $errors[] = 'operations.json is missing!';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        if ($is_ajax_req) {
            return $this->_ajax([
                'action' => $action,
                'redirect' => $redirect,
                'errors' => $errors,
                'op' => $op
            ]);
        } else {
            $this->_head('manage', $operation ? $operation['mission_name'] . ' (' . $operation['id'] . ')' : '');

            $this->load->view('admin/manage', [
                'operation' => $operation,
                'errors' => $errors,
                'op_in_db' => $op_in_db,
                'valid_event_types' => $valid_event_types,
                'should_ignore' => $should_ignore,
                'op' => $op,
                'file_size' => $file_size,
                'last_update' => $last_update
            ]);

            $this->_foot();
        }
    }

    private function _json_update($operation)
    {
        $errors = [];

        if (!download_file(OPERATION_DATA_JSON_URL_PATH . rawurlencode($operation['filename']), JSONPATH . $operation['filename'])) {
            $errors[] = 'Operation (' . $operation['id'] . ') data JSON download failed.';
            $this->_json_del($operation);
        } elseif (file_exists(JSONPATH . $operation['filename']) && filesize(JSONPATH . $operation['filename']) === 0) {
            $errors[] = 'The downloaded operation (' . $operation['id'] . ') data JSON was empty.';
            $this->_json_del($operation);
        }

        return $errors;
    }

    private function _json_del($operation)
    {
        if (file_exists(JSONPATH . $operation['filename'])) {
            unlink(JSONPATH . $operation['filename']);
        }
    }

    private function _parse_operation_json($operation)
    {
        if (!isset($operation['tag'])) {
            $operation['tag'] = '';
        }

        try {
            $markers = $this->operations->parse_markers($this->_parse_json(JSONPATH . $operation['filename'], '/Markers'));
        } catch (exception $e) {
            $markers = [
                'shots' => [],
                'events' => []
            ];
        }

        try {
            $addon_version = iterator_to_array($this->_parse_json(JSONPATH . $operation['filename'], '/addonVersion'));
            $operation['addon_version'] = isset($addon_version[0]) ? $addon_version[0] : '';
        } catch (exception $e) {
            $operation['addon_version'] = '';
        }

        try {
            $capture_delay = iterator_to_array($this->_parse_json(JSONPATH . $operation['filename'], '/captureDelay'));
            $operation['capture_delay'] = isset($capture_delay[0]) ? $capture_delay[0] : '';
        } catch (exception $e) {
            $operation['capture_delay'] = 1;
        }

        try {
            $extension_build = iterator_to_array($this->_parse_json(JSONPATH . $operation['filename'], '/extensionBuild'));
            $operation['extension_build'] = isset($extension_build[0]) ? $extension_build[0] : '';
        } catch (exception $e) {
            $operation['extension_build'] = '';
        }

        try {
            $extension_version = iterator_to_array($this->_parse_json(JSONPATH . $operation['filename'], '/extensionVersion'));
            $operation['extension_version'] = isset($extension_version[0]) ? $extension_version[0] : '';
        } catch (exception $e) {
            $operation['extension_version'] = '';
        }

        try {
            $mission_author = iterator_to_array($this->_parse_json(JSONPATH . $operation['filename'], '/missionAuthor'));
            $operation['mission_author'] = isset($mission_author[0]) ? $mission_author[0] : '';
        } catch (exception $e) {
            $operation['mission_author'] = '';
        }

        $times = null;
        try {
            $times = iterator_to_array($this->_parse_json(JSONPATH . $operation['filename'], '/times'));
            $operation['start_time'] = isset($times[0]) ? $times[0]['systemTimeUTC'] : '';
        } catch (exception $e) {
            $operation['start_time'] = '';
        }

        $entities = $this->operations->parse_entities($this->_parse_json(JSONPATH . $operation['filename'], '/entities'));
        $events = $this->operations->parse_events($this->_parse_json(JSONPATH . $operation['filename'], '/events'));

        $operation['end_winner'] = $events['end_winner'];
        $operation['end_message'] = $events['end_message'];

        return array(
            'markers' => [
                'shots' => $markers['shots']
            ],
            'details' => $operation,
            'entities' => $entities['entities'],
            'events' => array_merge($events['events'], $markers['events'], $entities['events']),
            'times' => is_array($times) ? $times : []
        );
    }

    private function _parse_json($path, $pointer = '') {
        require_once(APPPATH . 'third_party/json-machine/Autoloader.php');

        if ($this->_json_decoder === null) {
            $this->_json_decoder = new JsonMachine\JsonDecoder\ExtJsonDecoder(true, 512, JSON_BIGINT_AS_STRING);
        }

        return JsonMachine\Items::fromFile($path, ['pointer' => $pointer, 'decoder' => $this->_json_decoder]);
    }

    private function _ajax($data)
    {
        if (!$this->input->is_ajax_request()) {
            return show_error('Invalid request', 400);
        }

        $json_flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, $json_flags));
    }
}
