<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('X-Powered-By: 💖');
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

    public function login()
    {
        return $this->load->view('admin/login');
    }

    private function _head($active = 'ops', $prefix = '', $postfix = '')
    {
        return $this->load->view('head', [
            'title' => ($prefix ? $prefix . ' - ' : '') . $this->config->item('site_title') . ($postfix ? ' - ' . $postfix : ''),
            'main_menu' => $this->load->view('menu', ['active' => $active], true)
        ]);
    }

    private function _foot()
    {
        return $this->load->view('foot');
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

    private function _filters()
    {
        $events = $this->input->get('events');
        $event_type_ids = array_keys($this->config->item('event_types'));

        if (!is_null($events) && is_array($events)) {
            $re = current_url();
            if (count($events) > 0) {
                $events = array_filter($events, function ($v) use ($event_type_ids) {
                    return in_array($v, $event_type_ids);
                });

                if (count($events) > 0) {
                    $re .= '?events=' . implode(',', $events);
                }
            }

            return redirect($re);
        } else {
            if (!is_null($events)) {
                $events = explode(',', $events);

                if (count($events) > 0) {
                    $events = array_filter($events, function ($v) use ($event_type_ids) {
                        return in_array($v, $event_type_ids);
                    });
                }
            }

            if (!$events || count($events) === 0) {
                return $this->config->item('default_selected_event_types');
            } else {
                return $events;
            }
        }
    }

    public function index()
    {
        $this->ops();
    }

    public function players()
    {
        $this->_cache();

        $events_filter = $this->_filters();

        $this->load->model('players');

        $players = $this->players->get_players($events_filter);

        $this->_head('players');

        $this->load->view('filters', [
            'events_filter' => $events_filter
        ]);

        $this->load->view('players', [
            'items' => $players
        ]);

        $this->_foot();
    }

    public function ops()
    {
        $this->_cache();

        $events_filter = $this->_filters();

        $this->load->model('operations');

        $ops = $this->operations->get_ops($events_filter);

        $this->_head('ops');

        $this->load->view('filters', [
            'events_filter' => $events_filter
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
        $player_aliases = [];
        $player_ops = [];
        $player_roles = [];
        $player_weapons = [];
        $player_attackers = [];
        $player_victims = [];
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $player = $this->players->get_by_id($id);

            if ($player) {
                $player_aliases = $this->players->get_aliases_by_id($id);

                if ($tab === 'roles') {
                    $player_roles = $this->players->get_roles_by_id($id);
                } elseif ($tab === 'weapons') {
                    $player_weapons = $this->players->get_weapons_by_id($id);
                } elseif ($tab === 'attackers') {
                    $player_attackers = $this->players->get_attackers_by_id($id);
                } elseif ($tab === 'victims') {
                    $player_victims = $this->players->get_victims_by_id($id);
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
            } elseif ($tab === 'weapons') {
                $this->load->view('player-weapons', [
                    'items' => $player_weapons,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'weapons',
                        'player_url' => base_url('player/' . $player['id'])
                    ], true)
                ]);
            } elseif ($tab === 'attackers') {
                $this->load->view('player-attackers-victims', [
                    'items' => $player_attackers,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'attackers',
                        'player_url' => base_url('player/' . $player['id'])
                    ], true)
                ]);
            } elseif ($tab === 'victims') {
                $this->load->view('player-attackers-victims', [
                    'items' => $player_victims,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'victims',
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
        $op_weapons = [];
        $op_entities = [];
        if (filter_var($id, FILTER_VALIDATE_INT) || filter_var($id, FILTER_VALIDATE_INT) === 0) {
            $op = $this->operations->get_by_id($id);

            if ($op) {
                if ($tab === 'events') {
                    $op_events = $this->operations->get_events_by_id($id);
                } elseif ($tab === 'weapons') {
                    $op_weapons = $this->operations->get_weapons_by_id($id);
                } else { // entities
                    $op_entities = $this->operations->get_entities_by_id($id);
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
            } elseif ($tab === 'weapons') {
                $this->load->view('op-weapons', [
                    'items' => $op_weapons,
                    'op_menu' => $this->load->view('op-menu', [
                        'active' => 'weapons',
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

        $events_filter = $this->_filters();

        $this->load->model('players');

        $commanders = $this->players->get_commanders($events_filter);

        $this->_head('commanders');

        $this->load->view('filters', [
            'events_filter' => $events_filter
        ]);
        $this->load->view('commanders', [
            'items' => $commanders
        ]);

        $this->_foot();
    }

    public function assorted_data()
    {
        $this->_cache();

        $this->load->model('assorted');

        $this->_head('', 'Assorted data');

        $this->load->view('arrays-to-tables', [
            'tables' => [
                'Winners' => $this->assorted->get_winners(),
                'End messages' => $this->assorted->get_end_messages(),
                'Mission authors' => $this->assorted->get_mission_authors(),
                'Maps' => $this->assorted->get_maps(),
                'Groups' => $this->assorted->get_groups(),
                'Roles' => $this->assorted->get_roles(),
                'Weapons' => $this->assorted->get_weapons(),
                'Assets' => $this->assorted->get_vehicles()
            ]
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
