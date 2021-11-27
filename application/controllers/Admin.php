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
        return $this->load->view('foot');
    }

    public function clearcache()
    {
        $this->load->helper(['file', 'cache']);

        $clear = $this->input->post('clear_cache');
        if ($clear) {
            delete_all_cache();

            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] cleared site cache');
        }

        $re = $this->input->post('redirect') === 'add-alias' ? 'add-alias' : 'manage';

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
            }

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
        $ops_in_db = $this->operations->get_all_ops();

        if (file_exists(JSONPATH . 'operations.json')) {
            $last_update = filemtime(JSONPATH . 'operations.json');
            $file_size_bytes = filesize(JSONPATH . 'operations.json');

            if ($file_size_bytes > 0) {
                $file_size = $this->_convert_filesize($file_size_bytes);

                $operations = \JsonMachine\JsonMachine::fromFile(JSONPATH . 'operations.json');

                foreach ($operations as $o) {
                    $o['id'] = intval($o['id']);
                    if (!isset($o['tag']) && isset($o['type'])) {
                        $o['tag'] = $o['type'];
                    }
                    $o['__valid_event_types'] = $this->_get_valid_event_types($o);
                    $o['__should_ignore'] = $this->_should_be_ignored($o);
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

    public function manage($op_id = null)
    {
        require_once(APPPATH . 'third_party/json-machine/load.php');

        $this->load->helper('curl');
        $this->load->model('operations');

        $errors = [];
        $operation = null;
        $op_in_db = false;
        $valid_event_types = [];
        $should_ignore = false;
        $op = false;
        $file_size = '0 B';
        $last_update = 'none';

        if (file_exists(JSONPATH . 'operations.json')) {
            if (filter_var($op_id, FILTER_VALIDATE_INT) || filter_var($op_id, FILTER_VALIDATE_INT) === 0) {
                $operations = \JsonMachine\JsonMachine::fromFile(JSONPATH . 'operations.json');
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
                    $valid_event_types = $this->_get_valid_event_types($operation);
                    $should_ignore = $this->_should_be_ignored($operation);

                    // ID was posted
                    if (!is_null($this->input->post('id')) && $operation['id'] === intval($this->input->post('id'))) {
                        $action = $this->input->post('action');
                        $redirect = $this->input->post('redirect');

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
                                            $this->_manage_json_del($operation);
                                        }
                                    } elseif ($action === 'ignore' && $op_in_db === false) {
                                        if (!$this->operations->insert($operation)) {
                                            $errors[] = 'Failed to save operation.';
                                        }

                                        if (count($errors) === 0 && $redirect === 'list') {
                                            return redirect(base_url('manage/#id-' . $operation['id']));
                                        }
                                        $op_in_db = $this->operations->exists($operation['id']);

                                        if (!defined('MANAGE_DATA_JSON_FILES')) {
                                            $this->_manage_json_del($operation);
                                        }
                                    } elseif ($action === 'parse' && $op_in_db === false && count($valid_event_types) > 0) {
                                        $event_type = $this->input->post('event');
                                        if (in_array($event_type, $valid_event_types)) {
                                            $operation['event'] = $event_type;

                                            if (!file_exists(JSONPATH . $operation['filename']) && !defined('MANAGE_DATA_JSON_FILES')) {
                                                $err = $this->_manage_json_update($operation);
                                                $errors = array_merge($errors, $err);
                                            }

                                            if (file_exists(JSONPATH . $operation['filename'])) {
                                                $opdata = $this->_parse_operation_json($operation);
                                                $err = $this->operations->process_op_data($opdata['details'], $opdata['entities'], $opdata['events'], $opdata['markers']);
                                                $opdata = null;

                                                if (count($err) === 0 && !defined('MANAGE_DATA_JSON_FILES')) {
                                                    $this->_manage_json_del($operation);
                                                }
                                                $errors = array_merge($errors, $err);

                                                if (count($errors) === 0 && $redirect === 'list') {
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
                                        $err = $this->_manage_json_update($operation);
                                        $errors = array_merge($errors, $err);
                                    } elseif ($action === 'del' && defined('MANAGE_DATA_JSON_FILES')) {
                                        $this->_manage_json_del($operation);
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
                        $file_size = $this->_convert_filesize(filesize(JSONPATH . $operation['filename']));
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

    private function _should_be_ignored($operation)
    {
        if (function_exists('should_op_be_ignored')) {
            return should_op_be_ignored($operation);
        }

        return false;
    }

    private function _get_valid_event_types($operation)
    {
        if (isset($operation['tag'])) {
            $tag_event_types = array_change_key_case($this->config->item('tag_event_types'), CASE_LOWER);

            if (count($tag_event_types) > 0) {
                $op_tag = strtolower($operation['tag']);

                if (isset($tag_event_types[$op_tag])) {
                    $valid_event_types = $tag_event_types[$op_tag];

                    if (!is_array($valid_event_types)) {
                        $valid_event_types = [$valid_event_types];
                    }

                    return $valid_event_types;
                } else {
                    return [];
                }
            }
        }

        return array_keys($this->config->item('event_types'));
    }

    private function _manage_json_update($operation)
    {
        $errors = [];

        if (!download_file(OPERATION_DATA_JSON_URL_PATH . rawurlencode($operation['filename']), JSONPATH . $operation['filename'])) {
            $errors[] = 'Operation (' . $operation['id'] . ') data JSON download failed.';

            if (file_exists(JSONPATH . $operation['filename']) && filesize(JSONPATH . $operation['filename']) === 0) {
                unlink(JSONPATH . $operation['filename']);
            }
        }

        return $errors;
    }

    private function _manage_json_del($operation)
    {
        if (file_exists(JSONPATH . $operation['filename'])) {
            unlink(JSONPATH . $operation['filename']);
        }
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
            $markers = $this->operations->parse_markers(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/Markers'));
        } catch (exception $e) {
            $markers = [];
        }

        try {
            $addon_version = iterator_to_array(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/addonVersion'));
            $operation['addon_version'] = isset($addon_version[0]) ? $addon_version[0] : '';
        } catch (exception $e) {
            $operation['addon_version'] = '';
        }

        try {
            $capture_delay = iterator_to_array(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/captureDelay'));
            $operation['capture_delay'] = isset($capture_delay[0]) ? $capture_delay[0] : '';
        } catch (exception $e) {
            $operation['capture_delay'] = '';
        }

        try {
            $extension_build = iterator_to_array(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/extensionBuild'));
            $operation['extension_build'] = isset($extension_build[0]) ? $extension_build[0] : '';
        } catch (exception $e) {
            $operation['extension_build'] = '';
        }

        try {
            $extension_version = iterator_to_array(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/extensionVersion'));
            $operation['extension_version'] = isset($extension_version[0]) ? $extension_version[0] : '';
        } catch (exception $e) {
            $operation['extension_version'] = '';
        }

        try {
            $mission_author = iterator_to_array(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/missionAuthor'));
            $operation['mission_author'] = isset($mission_author[0]) ? $mission_author[0] : '';
        } catch (exception $e) {
            $operation['mission_author'] = '';
        }

        try {
            $start_time = iterator_to_array(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/times'));
            $operation['start_time'] = isset($start_time[0]) ? $start_time[0]['systemTimeUTC'] : '';
        } catch (exception $e) {
            $operation['start_time'] = '';
        }

        $entities = $this->operations->parse_entities(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/entities'));
        $events = $this->operations->parse_events(\JsonMachine\JsonMachine::fromFile(JSONPATH . $operation['filename'], '/events'));

        $operation['end_winner'] = $events['end_winner'];
        $operation['end_message'] = $events['end_message'];

        return array(
            'markers' => $markers,
            'details' => $operation,
            'entities' => $entities['entities'],
            'events' => array_merge($events['events'], $entities['events'])
        );
    }
}
