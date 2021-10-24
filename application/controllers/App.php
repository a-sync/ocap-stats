<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('X-Powered-By: 💖');
    }

    public function login()
    {
        return $this->load->view('admin/login');
    }

    public function zerosec($my_name = '', $my_hash = '')
    {
        $name = preg_replace("/[^A-Za-z0-9]/", '', $my_name);
        $this->load->library('session');

        if (strlen($name) < 3) {
            return redirect(base_url('login'));
        }

        $secret_hash = hash(
            'sha256',
            $this->config->item('admin_key')
                . '*-#<_§!?:)¤.-€@&,%/=(>\\|~ß$Ł' .
                $name
        );

        if ($my_hash === $this->config->item('admin_key')) {
            exit(base_url('login/' . $name . '/' . $secret_hash));
        }

        if ($my_hash !== $secret_hash) {
            return redirect(base_url('login'));
        }

        $this->session->admin = [
            'name' => $name
        ];
        session_write_close();
        log_message('error', 'EVENT --> Admin [' . $name . '] logging in @ ' . $this->input->ip_address());

        return redirect(base_url('manage'));
    }

    private function _head($active = 'players', $prefix = '', $postfix = '')
    {
        return $this->load->view('head', [
            'title' => ($prefix ? $prefix . ' - ' : '') . 'FNF Stats' . ($postfix ? ' - ' . $postfix : ''),
            'main_menu' => $this->load->view('menu', ['active' => $active], true)
        ]);
    }

    private function _foot($active = 'user')
    {
        return $this->load->view('foot', [
            'active' => $active
        ]);
    }

    private function _cache()
    {
        $this->load->helper('cache');

        $cache_expiration = 0;
        if (ENVIRONMENT === 'production') {
            $cache_expiration = 60 * 24 * 7;
        }

        $this->output->cache($cache_expiration);

        if ($cache_expiration) {
            $this->output->set_header('Cache-Control: max-age=0');
        } else {
            $this->output->set_header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            $this->output->set_header('Cache-Control: no-store, max-age=0');
        }
    }

    private function _filters_redirect()
    {
        $re = false;
        $events = $this->input->get('events');
        if (!is_null($events) && is_array($events)) {
            $re = current_url();
            if (count($events) > 0) {
                $event_type_ids = array_keys($this->config->item('event_types'));
                $events = array_filter($events, function ($v) use ($event_type_ids) {
                    return in_array($v, $event_type_ids);
                });

                if (count($events) > 0) {
                    $events_list = implode(',', $events);
                    $re .= '?events=' . $events_list;
                }
            }
        }

        return $re;
    }

    // players
    public function index()
    {
        $this->_cache();

        $re = $this->_filters_redirect();
        if ($re !== false) {
            return redirect($re);
        }

        $this->load->model('players');

        $event_type_ids = array_keys($this->config->item('event_types'));
        $default_events = ['eu', 'na'];
        $events = $this->input->get('events');

        if ($events !== null) {
            $events = explode(',', $events);

            if (count($events) > 0) {
                $events = array_filter($events, function ($v) use ($event_type_ids) {
                    return in_array($v, $event_type_ids);
                });
            }
        }

        if (!$events || count($events) === 0) {
            $events = $default_events;
        }

        $players = $this->players->get_players($events);

        $this->_head();

        $this->load->view('filters', [
            'events' => $events
        ]);

        $this->load->view('players', [
            'items' => $players
        ]);

        $this->_foot();
    }

    public function ops()
    {
        $this->_cache();

        $re = $this->_filters_redirect();
        if ($re !== false) {
            return redirect($re);
        }

        $this->load->model('operations');

        $event_type_ids = array_keys($this->config->item('event_types'));
        $default_events = ['eu', 'na'];
        $event_types = $this->input->get('events');

        if ($event_types !== null) {
            $event_types = explode(',', $event_types);

            if (count($event_types) > 0) {
                $event_types = array_filter($event_types, function ($v) use ($event_type_ids) {
                    return in_array($v, $event_type_ids);
                });
            }
        }

        if (!$event_types || count($event_types) === 0) {
            $event_types = $default_events;
        }

        $ops = $this->operations->get_ops($event_types);

        $this->_head('ops');

        $this->load->view('filters', [
            'events' => $event_types
        ]);
        $this->load->view('ops', [
            'items' => $ops
        ]);

        $this->_foot();
    }

    public function player($id, $tab = 'ops')
    {
        $this->_cache();

        $this->load->model('players');

        $errors = [];
        $player = null;
        $player_ops = [];
        $player_roles = [];
        $player_aliases = [];
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $player = $this->players->get_by_id($id);

            if ($player) {
                $player_aliases = $this->players->get_aliases_by_id($id);

                if ($tab === 'roles') {
                    $player_roles = $this->players->get_roles_by_id($id);
                } else { //ops
                    $player_ops = $this->players->get_ops_by_id($id);
                }
            } else {
                $errors[] = 'Unknown player ID given.';
            }
        } else {
            $errors[] = 'Invalid ID given.';
        }

        $this->_head('players', $player ? $player['name'] : '');

        $this->load->view('player', [
            'player' => $player,
            'errors' => $errors,
            'aliases' => $player_aliases
        ]);

        if ($player) {
            if ($tab === 'roles') {
                $this->load->view('player-roles', [
                    'items' => $player_roles,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'roles',
                        'player_url' => base_url('player/' . $player['id'])
                    ], true)
                ]);
            } else { // ops
                $this->load->view('player-ops', [
                    'items' => $player_ops,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'ops',
                        'player_url' => base_url('player/' . $player['id'])
                    ], true)
                ]);
            }
        }

        $this->_foot();
    }

    public function op($id, $tab = 'entities')
    {
        $this->_cache();

        $this->load->model('operations');

        $errors = [];
        $op = null;
        $op_events = [];
        $op_entities = [];
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $op = $this->operations->get_by_id($id);

            if ($op) {
                if ($tab === 'events') {
                    $op_events = $this->operations->get_events_by_op_id($id);
                } else { // entities
                    $op_entities = $this->operations->get_entities_by_op_id($id);
                }
            } else {
                $errors[] = 'Unknown op ID given.';
            }
        } else {
            $errors[] = 'Invalid ID given.';
        }

        $this->_head('ops', $op ? str_replace('_', ' ', $op['mission_name']) : '');

        $this->load->view('op', [
            'op' => $op,
            'errors' => $errors
        ]);

        if ($op) {
            if ($tab === 'events') {
                $this->load->view('op-events', [
                    'items' => $op_events,
                    'op_menu' => $this->load->view('op-menu', [
                        'active' => 'events',
                        'op_url' => base_url('op/' . $op['id'])
                    ], true)
                ]);
            } else { // entities
                $this->load->view('op-entities', [
                    'items' => $op_entities,
                    'op_menu' => $this->load->view('op-menu', [
                        'active' => 'entities',
                        'op_url' => base_url('op/' . $op['id'])
                    ], true)
                ]);
            }
        }

        $this->_foot();
    }

    public function commanders()
    {
        $this->_cache();

        $re = $this->_filters_redirect();
        if ($re !== false) {
            return redirect($re);
        }

        $this->load->model('players');

        $event_type_ids = array_keys($this->config->item('event_types'));
        $default_events = ['eu', 'na'];
        $event_types = $this->input->get('events');

        if ($event_types !== null) {
            $event_types = explode(',', $event_types);

            if (count($event_types) > 0) {
                $event_types = array_filter($event_types, function ($v) use ($event_type_ids) {
                    return in_array($v, $event_type_ids);
                });
            }
        }

        if (!$event_types || count($event_types) === 0) {
            $event_types = $default_events;
        }

        $commanders = $this->players->get_commanders($event_types);

        $this->_head('commanders');

        $this->load->view('filters', [
            'events' => $event_types
        ]);
        $this->load->view('commanders', [
            'items' => $commanders
        ]);

        $this->_foot();
    }

    public function readme_md()
    {
        $this->_cache();

        $this->_head('', 'About');

        $this->load->library('markdown');

        $this->load->view('md', [
            'markdown' => $this->markdown->transform_file(APPPATH . '../README.md')
        ]);

        $this->_foot();
    }

    private function _ajax($data)
    {
        if (!$this->input->is_ajax_request()) {
            return show_error(400);
        }

        $dbg = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, $dbg));
    }
}
