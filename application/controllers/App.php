<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('X-Powered-By: 🐹🐹');
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
            log_message('error', 'EVENT --> Admin key used for [' . $name . '] @ ' . $this->input->ip_address());
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

    public function favicon_ico()
    {
        show_404('', false);
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

            if (!is_array($events) || count($events) === 0) {
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

        $this->_head('players', 'Players');

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
        $this->load->model('additional_data');

        $errors = [];
        $player = null;
        $player_aliases = [];
        $player_ops = [];
        $player_roles = [];
        $player_weapons = [];
        $player_attackers = [];
        $player_victims = [];
        $player_cmd_stats = [
            'rivals' => [],
            'commanded_ops' => []
        ];
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $player = $this->players->get_by_id($id);

            if ($player) {
                $player_aliases = $this->additional_data->get_aliases($id);
                // TODO: get aliases from entities if player has a uid
                $player_cmd_stats = $this->additional_data->get_player_cmd_stats($player['id']);

                if ($tab === 'roles') {
                    $player_roles = $this->players->get_roles_by_id($id);
                } elseif ($tab === 'weapons') {
                    $player_weapons = $this->players->get_weapons_by_id($id);
                } elseif ($tab === 'attackers') {
                    $player_attackers = $this->players->get_attackers_by_id($id);
                } elseif ($tab === 'victims') {
                    $player_victims = $this->players->get_victims_by_id($id);
                } else { // ops
                    $player_ops = $this->players->get_ops_by_id($id);
                }
            } else {
                $alias_of = $this->players->get_alias_of_by_id($id);
                if ($alias_of) {
                    return redirect(base_url('player/' . $alias_of));
                }

                $errors[] = 'Unknown player ID given.';
            }
        } else {
            $errors[] = 'Invalid ID given.';
        }

        $this->_head('players', $player ? $player['name'] : '');

        $this->load->view('player', [
            'player' => $player,
            'errors' => $errors,
            'aliases' => $player_aliases,
            'commanded_ops' => $player_cmd_stats['commanded_ops']
        ]);

        if ($player) {
            $show_rivals = boolval(count($player_cmd_stats['rivals']));

            if ($tab === 'roles') {
                $this->load->view('player-roles', [
                    'items' => $player_roles,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'roles',
                        'player_url' => base_url('player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true)
                ]);
            } elseif ($tab === 'weapons') {
                $this->load->view('player-weapons', [
                    'items' => $player_weapons,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'weapons',
                        'player_url' => base_url('player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true)
                ]);
            } elseif ($tab === 'attackers') {
                $this->load->view('player-attackers-victims', [
                    'items' => $player_attackers,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'attackers',
                        'player_url' => base_url('player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true),
                    'tab' => 'attackers'
                ]);
            } elseif ($tab === 'victims') {
                $this->load->view('player-attackers-victims', [
                    'items' => $player_victims,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'victims',
                        'player_url' => base_url('player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true),
                    'tab' => 'victims'
                ]);
            } elseif ($tab === 'rivals') {
                $this->load->view('player-rivals', [
                    'items' => $player_cmd_stats['rivals'],
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'rivals',
                        'player_url' => base_url('player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true),
                    'tab' => 'victims'
                ]);
            } else { // ops
                $this->load->view('player-ops', [
                    'items' => $player_ops,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'ops',
                        'player_url' => base_url('player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true),
                    'commanded_ops' => $player_cmd_stats['commanded_ops']
                ]);
            }
        }

        $this->_foot();
    }

    public function op($id, $tab = 'entities')
    {
        $this->_cache();

        $this->load->model('operations');
        $this->load->model('additional_data');

        $errors = [];
        $op = false;
        $op_commanders = [];
        $op_sides = [];
        $op_events = [];
        $op_weapons = [];
        $op_entities = [];
        $players_first_op = [];
        if (filter_var($id, FILTER_VALIDATE_INT) || filter_var($id, FILTER_VALIDATE_INT) === 0) {
            $op = $this->operations->get_by_id($id);

            if ($op) {
                $op_commanders_data = $this->additional_data->get_commanders(true, $op['id'], true);
                if (isset($op_commanders_data['resolved'][$op['id']])) {
                    $op_commanders = $op_commanders_data['resolved'][$op['id']];
                }

                $op_sides = $this->additional_data->get_op_sides($op['id']);

                $op_player_ids = [];
                if ($tab === 'events') {
                    $op_events = $this->operations->get_events_by_id($op['id']);
                    if (count($op_events) > 0) {
                        $attacker_player_ids = array_column($op_events, 'attacker_player_id', 'attacker_player_id');
                        $victim_player_ids = array_column($op_events, 'victim_player_id', 'victim_player_id');
                        $op_player_ids = array_unique(array_merge($attacker_player_ids, $victim_player_ids), SORT_NUMERIC);
                    }
                } elseif ($tab === 'weapons') {
                    $op_weapons = $this->operations->get_weapons_by_id($op['id']);
                } else { // entities
                    $op_entities = $this->operations->get_entities_by_id($op['id']);
                    if (count($op_entities) > 0) {
                        $op_player_ids = array_unique(array_column($op_entities, 'player_id'), SORT_NUMERIC);
                    }
                }

                if (count($op_player_ids) > 0) {
                    $players_first_op = $this->additional_data->get_first_ops_of_players($op_player_ids);
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
            'errors' => $errors,
            'op_commanders' => $op_commanders,
            'op_sides' => $op_sides
        ]);

        if ($op) {
            if ($tab === 'events') {
                $this->load->view('op-events', [
                    'items' => $op_events,
                    'op_menu' => $this->load->view('op-menu', [
                        'active' => 'events',
                        'op_url' => base_url('op/' . $op['id'])
                    ], true),
                    'op_commanders' => $op_commanders,
                    'op_id' => $op['id'],
                    'players_first_op' => $players_first_op
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
                    ], true),
                    'op_commanders' => $op_commanders,
                    'op_id' => $op['id'],
                    'players_first_op' => $players_first_op
                ]);
            }
        }

        $this->_foot();
    }

    public function commanders()
    {
        $this->_cache();

        $events_filter = $this->_filters();

        $this->load->model('additional_data');

        $commanders = $this->additional_data->get_commanders($events_filter);

        $this->_head('commanders', 'Commanders');

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

        $this->load->library('markdown');

        $this->_head('', 'About');

        $this->load->view('md', [
            'markdown' => $this->markdown->transform_file(APPPATH . '../README.md')
        ]);

        $this->_foot();
    }
}
