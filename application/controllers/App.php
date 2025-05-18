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

    private function _head($active = 'ops', $prefix = '', $postfix = '', $year = false)
    {
        return $this->load->view('head', [
            'title' => ($prefix ? $prefix . ' - ' : '') . $this->config->item('site_title') . ($postfix ? ' - ' . $postfix : ''),
            'main_menu' => $this->load->view('menu', ['active' => $active, 'year' => $year], true)
        ]);
    }

    private function _foot($path = '', $year = false)
    {
        $this->load->model('additional_data');
        $years = $this->additional_data->get_ops_years();

        return $this->load->view('foot', [
            'year' => $year,
            'years' => $years,
            'path' => $path
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

    public function favicon_ico()
    {
        show_404('', false);
    }

    private function _event_type_selection($available_event_types)
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
                $default_selection = $this->config->item('default_selected_event_types');

                return array_filter($default_selection, function ($v) use ($available_event_types) {
                    return in_array($v, $available_event_types);
                });
            } else {
                return $events;
            }
        }
    }

    public function index()
    {
        $this->ops();
    }

    public function players($year = false)
    {
        $this->_cache();

        $this->load->model('players');
        $this->load->model('additional_data');

        $available_event_types = $this->additional_data->get_ops_event_type_ids($year);
        $selected_event_types = $this->_event_type_selection($available_event_types);
        $players = $this->players->get_players($selected_event_types, false, $year);

        $this->_head('players', 'Players', '', $year);

        $this->load->view('filters', [
            'available_event_types' => $available_event_types,
            'selected_event_types' => $selected_event_types
        ]);

        $this->load->view('players', [
            'year' => $year,
            'items' => $players
        ]);

        $this->_foot('players', $year);
    }

    public function ops($year = false)
    {
        $this->_cache();

        $this->load->model('operations');
        $this->load->model('additional_data');

        $available_event_types = $this->additional_data->get_ops_event_type_ids($year);
        $selected_event_types = $this->_event_type_selection($available_event_types);
        $ops = $this->operations->get_ops($selected_event_types, false, $year);

        $this->_head('ops', 'Opeations', '', $year);

        $this->load->view('filters', [
            'available_event_types' => $available_event_types,
            'selected_event_types' => $selected_event_types
        ]);
        $this->load->view('ops', [
            'year' => $year,
            'items' => $ops
        ]);

        $this->_foot('', $year);
    }

    public function player($id, $tab = 'ops', $year = false)
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
        $year_prefix = $year !== false ? $year . '/' : '';
        $path = 'players';

        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $path = 'player/' . $id;
            if ($tab !== 'ops') $path .= '/' . $tab;
            $player = $this->players->get_by_id($id, $year);

            if ($player) {
                $player_aliases = $this->additional_data->get_aliases($id);
                // TODO: get aliases from entities if player has a uid
                $player_cmd_stats = $this->additional_data->get_player_cmd_stats($player['id'], $year);

                if ($tab === 'roles') {
                    $player_roles = $this->players->get_roles_by_id($id, $year);
                } elseif ($tab === 'weapons') {
                    $player_weapons = $this->players->get_weapons_by_id($id, $year);
                } elseif ($tab === 'attackers') {
                    $player_attackers = $this->players->get_attackers_by_id($id, $year);
                } elseif ($tab === 'victims') {
                    $player_victims = $this->players->get_victims_by_id($id, $year);
                } else { // ops
                    $player_ops = $this->players->get_ops_by_id($id, $year);
                }
            } else {
                $player_recheck = $year === false ? false : $this->players->get_by_id($id);
                if ($player_recheck) {
                    $errors[] = 'No data available for this player in ' . $year;
                } else {
                    $alias_of = $this->players->get_alias_of_by_id($id);
                    if ($alias_of) {
                        return redirect(base_url($year_prefix . 'player/' . $alias_of));
                    } else {
                        $errors[] = 'Unknown player ID given.';
                    }
                }
            }
        } else {
            $errors[] = 'Invalid ID given.';
        }

        $this->_head('players', $player ? $player['name'] : '', '', $year);

        $this->load->view('player', [
            'year' => $year,
            'player' => $player,
            'errors' => $errors,
            'aliases' => $player_aliases,
            'commanded_ops' => $player_cmd_stats['commanded_ops']
        ]);

        if ($player) {
            $show_rivals = boolval(count($player_cmd_stats['rivals']));

            if ($tab === 'roles') {
                $this->load->view('player-roles', [
                    'year' => $year,
                    'items' => $player_roles,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'roles',
                        'player_url' => base_url($year_prefix . 'player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true)
                ]);
            } elseif ($tab === 'weapons') {
                $this->load->view('player-weapons', [
                    'year' => $year,
                    'items' => $player_weapons,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'weapons',
                        'player_url' => base_url($year_prefix . 'player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true)
                ]);
            } elseif ($tab === 'attackers') {
                $this->load->view('player-attackers-victims', [
                    'year' => $year,
                    'items' => $player_attackers,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'attackers',
                        'player_url' => base_url($year_prefix . 'player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true),
                    'tab' => 'attackers'
                ]);
            } elseif ($tab === 'victims') {
                $this->load->view('player-attackers-victims', [
                    'year' => $year,
                    'items' => $player_victims,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'victims',
                        'player_url' => base_url($year_prefix . 'player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true),
                    'tab' => 'victims'
                ]);
            } elseif ($tab === 'rivals') {
                $this->load->view('player-rivals', [
                    'year' => $year,
                    'items' => $player_cmd_stats['rivals'],
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'rivals',
                        'player_url' => base_url($year_prefix . 'player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true)
                ]);
            } else { // ops
                $this->load->view('player-ops', [
                    'year' => $year,
                    'items' => $player_ops,
                    'player_menu' => $this->load->view('player-menu', [
                        'active' => 'ops',
                        'player_url' => base_url($year_prefix . 'player/' . $player['id']),
                        'show_rivals' => $show_rivals
                    ], true),
                    'commanded_ops' => $player_cmd_stats['commanded_ops']
                ]);
            }
        }

        $this->_foot($path, $year);
    }

    public function op($id, $tab = 'entities', $year = false)
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
        $year_prefix = $year !== false ? $year . '/' : '';
        if (filter_var($id, FILTER_VALIDATE_INT) || filter_var($id, FILTER_VALIDATE_INT) === 0) {
            $op = $this->operations->get_by_id($id, true, $year);

            if ($op) {
                $op_commanders_data = $this->additional_data->get_commanders(true, $op['id'], true, $year);
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

                        $op_entities = $this->additional_data->get_op_entities($op['id']);
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

        $this->_head('ops', $op ? str_replace('_', ' ', $op['mission_name']) : '', '', $year);

        $this->load->view('op', [
            'year' => $year,
            'op' => $op,
            'errors' => $errors,
            'op_commanders' => $op_commanders,
            'op_sides' => $op_sides
        ]);

        if ($op) {
            if ($tab === 'events') {
                $this->load->view('op-events', [
                    'year' => $year,
                    'items' => $op_events,
                    'op_menu' => $this->load->view('op-menu', [
                        'active' => 'events',
                        'op_url' => base_url($year_prefix . 'op/' . $op['id'])
                    ], true),
                    'op_commanders' => $op_commanders,
                    'op_id' => $op['id'],
                    'players_first_op' => $players_first_op,
                    'op_entities' => $op_entities
                ]);
            } elseif ($tab === 'weapons') {
                $this->load->view('op-weapons', [
                    'year' => $year,
                    'items' => $op_weapons,
                    'op_menu' => $this->load->view('op-menu', [
                        'active' => 'weapons',
                        'op_url' => base_url($year_prefix . 'op/' . $op['id'])
                    ], true)
                ]);
            } else { // entities
                $this->load->view('op-entities', [
                    'year' => $year,
                    'items' => $op_entities,
                    'op_menu' => $this->load->view('op-menu', [
                        'active' => 'entities',
                        'op_url' => base_url($year_prefix . 'op/' . $op['id'])
                    ], true),
                    'op_commanders' => $op_commanders,
                    'op_id' => $op['id'],
                    'players_first_op' => $players_first_op
                ]);
            }
        }

        $this->_foot('', $year);
    }

    public function commanders($year = false)
    {
        $this->_cache();

        $this->load->model('additional_data');

        $available_event_types = $this->additional_data->get_ops_event_type_ids($year);
        $selected_event_types = $this->_event_type_selection($available_event_types);
        $commanders = $this->additional_data->get_commanders($selected_event_types, false, false, $year);

        $this->_head('commanders', 'Commanders', '', $year);

        $this->load->view('filters', [
            'available_event_types' => $available_event_types,
            'selected_event_types' => $selected_event_types
        ]);
        $this->load->view('commanders', [
            'year' => $year,
            'items' => $commanders
        ]);

        $this->_foot('commanders', $year);
    }

    public function assorted_data($year = false)
    {
        $this->_cache();

        $this->load->model('assorted');

        $this->_head('', 'Assorted data', '', $year);

        $this->load->view('arrays-to-tables', [
            'title' => 'Assorted data' . ($year !== false ? ' for ' . $year : ''),
            'tables' => [
                'Winners' => $this->assorted->get_winners($year),
                'End messages' => $this->assorted->get_end_messages($year),
                'Mission authors' => $this->assorted->get_mission_authors($year),
                'Maps' => $this->assorted->get_maps($year),
                'Groups' => $this->assorted->get_groups($year),
                'Roles' => $this->assorted->get_roles($year),
                'Weapons' => $this->assorted->get_weapons($year),
                'Assets' => $this->assorted->get_vehicles($year)
            ]
        ]);

        $this->_foot('assorted-data', $year);
    }

    public function readme_md($year = false)
    {
        $this->_cache();

        $this->load->library('markdown');

        $this->_head('about', 'About', '', $year);

        $this->load->view('md', [
            'markdown' => $this->markdown->transform_file(APPPATH . '../README.md')
        ]);

        $this->_foot('about', $year);
    }
}
