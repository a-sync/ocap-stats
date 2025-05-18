<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Data extends CI_Controller
{
    private $adminUser = false;

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

        session_write_close();
    }

    private function _head($active = 'add-alias', $prefix = '', $postfix = '')
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

    public function index()
    {
        $this->add_alias();
    }

    public function add_alias()
    {
        $this->load->helper('cache');
        $this->load->model('additional_data');

        $errors = [];
        $alias_of = $this->input->get('alias_of');
        $add_alias = $this->input->post('add_alias');
        $add_new_player = $this->input->post('add_new_player');
        $new_player_name = $this->input->post('new_player_name', true);
        $player_id = $this->input->post('player_id');
        $alias_ids = $this->input->post('aliases');
        $past_ops = intval($this->input->get('past_ops'));

        if ($past_ops < 6) {
            $past_ops = 6;
        }

        if (!is_array($alias_ids)) {
            $alias_ids = [];
        }

        if (!is_null($alias_of)) {
            $current_aliases = $this->additional_data->get_aliases($alias_of);

            return $this->_ajax(array_column($current_aliases, 'id'));
        } elseif ($add_alias) {
            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] updating aliases of ' . $player_id . ' (' . implode(', ', $alias_ids) . ')');

            if (filter_var($player_id, FILTER_VALIDATE_INT)) {
                $err = $this->additional_data->update_aliases($player_id, $alias_ids);
                $errors = array_merge($errors, $err);
            } else {
                $errors[] = 'Invalid player ID given!';
            }
        } elseif ($add_new_player) {
            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] adding player ' . $new_player_name);

            if (!is_null($new_player_name) && $new_player_name !== '') {
                $re = $this->additional_data->add_new_player($new_player_name);
                $errors = array_merge($errors, $re['errors']);
                if ($re['player_id']) {
                    $player_id = $re['alias_of'] ? strval($re['alias_of']) : strval($re['player_id']);
                }
            } else {
                $errors[] = 'Player name can not be empty!';
            }
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        $players = array_map(function ($p) {
            if ($p['uid'] === null) {
                unset($p['uid']);
            }
            if ($p['alias_of'] === '0') {
                unset($p['alias_of']);
            }
            return $p;
        }, $this->additional_data->get_players_names());

        $new_names = $this->additional_data->get_new_names($past_ops);

        $last_cache_update = 0;
        $homepage_cache_file = get_cache_file('');
        if (file_exists($homepage_cache_file)) {
            $last_cache_update = filemtime($homepage_cache_file);
        }

        $this->_head('add-alias', 'Add player alias');

        $this->load->view('admin/add-alias', [
            'players' => $players,
            'errors' => $errors,
            'player_id' => $player_id,
            'new_names' => $new_names,
            'last_cache_update' => $last_cache_update,
            'past_ops' => $past_ops
        ]);

        $this->_foot();
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

    public function fix_data($tab = 'missing')
    {
        $this->load->helper('cache');
        $this->load->model('additional_data');

        $errors = [];
        $ops_sus_suicides = [];
        $ops_leads_all = $this->additional_data->get_commanders(true, false, true);
        $op_ids_with_resolved_cmd = array_keys(array_filter($ops_leads_all['resolved'], function ($v) {
            return count($v) > 1;
        }));

        if ($tab === 'verified') {
            $ops = $this->additional_data->get_ops_to_fix_data(true);
            $ops_sus_suicides = $this->additional_data->get_ops_sus_suicides(array_column($ops, 'id'));
        } elseif ($tab === 'unverified') {
            $ops = $this->additional_data->get_ops_to_fix_data(false);
            $ops_sus_suicides = $this->additional_data->get_ops_sus_suicides(array_column($ops, 'id'));
        } else { // sus
            $ops_sus_suicides = $this->additional_data->get_ops_sus_suicides();
            $op_ids_with_sus_suicides = array_keys($ops_sus_suicides);
            $ops = $this->additional_data->get_ops_to_fix_data(false, $op_ids_with_resolved_cmd, $op_ids_with_sus_suicides);
        }

        $last_cache_update = 0;
        $homepage_cache_file = get_cache_file('');
        if (file_exists($homepage_cache_file)) {
            $last_cache_update = filemtime($homepage_cache_file);
        }

        $this->_head('fix-data', 'Fix missing OP data');

        $this->load->view('admin/fix-data', [
            'items' => $ops,
            'cmd_unambiguous' => $ops_leads_all['unambiguous'],
            'cmd_verified' => $ops_leads_all['verified'],
            'errors' => $errors,
            'tab' => $tab,
            'last_cache_update' => $last_cache_update,
            'ops_sus_suicides' => $ops_sus_suicides
        ]);

        $this->_foot();
    }

    public function verify($op_id = null)
    {
        $this->load->model('operations');
        $this->load->model('additional_data');

        $errors = [];
        $op = false;
        $op_sides = [];
        $op_player_entities = [];
        $op_commanders_data = [];
        if (filter_var($op_id, FILTER_VALIDATE_INT) || filter_var($op_id, FILTER_VALIDATE_INT) === 0) {
            $op = $this->operations->get_by_id($op_id);

            if ($op) {
                $op_sides = $this->additional_data->get_op_sides($op['id']);
                $op_player_entities = $this->additional_data->get_op_player_entities_simple($op['id']);
                $op_commanders_data = $this->additional_data->get_commanders(true, $op['id'], true);

                // ID was posted
                if (!is_null($this->input->post('id')) && $op['id'] === $this->input->post('id')) {
                    $action = $this->input->post('action');
                    $redirect = $this->input->post('redirect');
                    $valid_event_types = get_valid_event_types($op);
                    $op_verified = intval($op['verified']);

                    log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] operation (' . $op['id'] . ') action: ' . $action);

                    try {
                        if ($action === 'update') {
                            $op_upd = [
                                'id' => $op['id'],
                                'verified' => 0
                            ];

                            if ($op_verified === 0 && !$redirect) {
                                $start_time = $this->input->post('start_time');
                                if (preg_match('/^[0-2][0-9]{3}-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/', $start_time) && strtotime($start_time) !== false) {
                                    $op_upd['start_time'] = gmdate('Y-m-d H:i:s', strtotime($start_time));
                                } else {
                                    $errors[] = 'Invalid start time format!';
                                }

                                $event_type = $this->input->post('event');
                                if (in_array($event_type, $valid_event_types)) {
                                    $op_upd['event'] = $event_type;
                                } else {
                                    $errors[] = 'Invalid event type for this operation!';
                                }

                                $op_upd['mission_author'] = '';
                                if (strval($this->input->post('mission_author', true)) !== '') {
                                    $op_upd['mission_author'] = substr(strval($this->input->post('mission_author', true)), 0, 255);
                                }

                                $op_upd['end_winner'] = '';
                                $end_winner = $this->input->post('end_winner');
                                if (is_array($end_winner) && count($end_winner) > 0) {
                                    $winners = [];
                                    foreach ($end_winner as $w) {
                                        if ($w !== '' && isset($op_sides[$w])) {
                                            $winners[] = $w;
                                        }
                                    }
                                    $op_upd['end_winner'] = implode('/', $winners);
                                }

                                $op_upd['end_message'] = '';
                                if (strval($this->input->post('end_message', true)) !== '') {
                                    $op_upd['end_message'] = substr(strval($this->input->post('end_message', true)), 0, 255);
                                }

                                $cmd = $this->input->post('cmd');
                                if (is_array($cmd)) {
                                    $entities_upd = [];
                                    foreach ($op_sides as $s => $pc) {
                                        if ($pc > 0) {
                                            if (isset($cmd[$s]) && is_numeric($cmd[$s])) {
                                                if (intval($cmd[$s]) === -1) {
                                                    if (isset($op_commanders_data['unambiguous'][$op['id']][$s])) {
                                                        $entities_upd[] = [
                                                            'id' => $op_commanders_data['unambiguous'][$op['id']][$s]['entity_id'],
                                                            'cmd' => 0
                                                        ];
                                                    }
                                                } else {
                                                    $entities_upd[] = [
                                                        'id' => intval($cmd[$s]),
                                                        'cmd' => 1
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    $err = $this->additional_data->update_op_commanders($op['id'], $entities_upd);
                                    $errors = array_merge($errors, $err);
                                }
                            }

                            if (intval($this->input->post('verified')) === 1) {
                                $op_upd['verified'] = 1;
                            } elseif ($op_verified === 1) {
                                $op_upd = [
                                    'id' => $op['id'],
                                    'verified' => 0
                                ];
                            }

                            if ($op_verified === 0 || ($op_verified === 1 && $op_upd['verified'] === 0)) {
                                $err = $this->additional_data->update_op($op_upd);
                                $errors = array_merge($errors, $err);
                            }

                            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] operation (' . $op['id'] . ') update finished (errors: ' . count($errors) . ')');

                            if (count($errors) === 0 && in_array($redirect, ['entities', 'events'])) {
                                return redirect(base_url('manage/' . $op['id'] . '/' . $redirect));
                            }

                            // Reload updated data
                            $op = $this->operations->get_by_id($op['id']);
                            $op_sides = $this->additional_data->get_op_sides($op['id']);
                            $op_player_entities = $this->additional_data->get_op_player_entities_simple($op['id']);
                            $op_commanders_data = $this->additional_data->get_commanders(true, $op['id'], true);
                        } else {
                            $errors[] = 'Invalid action.';
                        }
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            } else {
                $errors[] = 'Unknown operation ID given.';
            }
        } else {
            $errors[] = 'Invalid operation ID given.';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        $this->_head('fix-data', $op ? $op['mission_name'] . ' (' . $op['id'] . ')' : '');

        $this->load->view('admin/verify', [
            'errors' => $errors,
            'op' => $op,
            'op_sides' => $op_sides,
            'op_player_entities' => $op_player_entities,
            'cmd_resolved' => $op ? element($op['id'], $op_commanders_data['resolved'], []) : [],
            'cmd_verified' => $op ? element($op['id'], $op_commanders_data['verified'], []) : [],
            'cmd_unambiguous' => $op ? element($op['id'], $op_commanders_data['unambiguous'], []) : [],
            'cmd_ambiguous' => $op ? element($op['id'], $op_commanders_data['ambiguous'], []) : []
        ]);

        $this->_foot();
    }

    public function entities($op_id = null)
    {
        $this->load->model('operations');
        $this->load->model('additional_data');

        $errors = [];
        $op = false;
        $op_sides = [];
        $op_commanders = [];
        $op_entities = [];
        $players_first_op = [];
        if (filter_var($op_id, FILTER_VALIDATE_INT) || filter_var($op_id, FILTER_VALIDATE_INT) === 0) {
            $op = $this->operations->get_by_id($op_id);

            if ($op) {
                $op_commanders_data = $this->additional_data->get_commanders(true, $op['id'], true);
                if (isset($op_commanders_data['resolved'][$op['id']])) {
                    $op_commanders = $op_commanders_data['resolved'][$op['id']];
                }

                $op_sides = $this->additional_data->get_op_sides($op['id']);

                $op_entities = $this->additional_data->get_op_player_entities($op['id']);

                $op_player_ids = [];
                if (count($op_entities) > 0) {
                    $op_player_ids = array_unique(array_column($op_entities, 'player_id'), SORT_NUMERIC);

                    $op_entities = $this->additional_data->calculate_entities_sus_factor($op_entities);
                }

                if (count($op_player_ids) > 0) {
                    $players_first_op = $this->additional_data->get_first_ops_of_players($op_player_ids);
                }

            } else {
                $errors[] = 'Unknown operation ID given.';
            }
        } else {
            $errors[] = 'Invalid operation ID given.';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        $this->_head('fix-data', $op ? $op['mission_name'] . ' (' . $op['id'] . ')' : '');

        $this->load->view('admin/entities', [
            'errors' => $errors,
            'op' => $op,
            'items' => $op_entities,
            'op_sides' => $op_sides,
            'op_commanders' => $op_commanders,
            'players_first_op' => $players_first_op
        ]);

        $this->_foot();
    }
    
    public function events($op_id = null)
    {
        $this->load->model('operations');
        $this->load->model('additional_data');
        $this->load->model('players');

        $errors = [];
        $op = false;
        $op_sides = [];
        $op_commanders = [];
        $op_events = [];
        $op_entities = [];
        $entity_id = false;
        $player_id = false;
        $player = false;
        $op_sus_suicides = [];
        if (filter_var($op_id, FILTER_VALIDATE_INT) || filter_var($op_id, FILTER_VALIDATE_INT) === 0) {
            $op = $this->operations->get_by_id($op_id);

            if ($op) {
                $op_commanders_data = $this->additional_data->get_commanders(true, $op['id'], true);
                if (isset($op_commanders_data['resolved'][$op['id']])) {
                    $op_commanders = $op_commanders_data['resolved'][$op['id']];
                }

                $op_sides = $this->additional_data->get_op_sides($op['id']);

                $entity_id = filter_var($this->input->get('entity_id'), FILTER_VALIDATE_INT);
                if (!$entity_id && $entity_id !== 0) {
                    $entity_id = false;
                }

                $player_id = filter_var($this->input->get('player_id'), FILTER_VALIDATE_INT);
                if (!$player_id && $player_id !== 0) {
                    $player_id = false;
                } else {
                    $player = $this->players->get_by_id($player_id);
                }

                $op_events = $this->operations->get_events_by_id($op['id'], $entity_id, $player_id);

                $op_sus_suicides = array_reduce($this->additional_data->get_ops_sus_suicides([$op['id']]), function ($carry, $value) {
                    return array_merge($carry, $value);
                }, []);

                $op_entities = $this->additional_data->get_op_entities($op['id']);
            } else {
                $errors[] = 'Unknown operation ID given.';
            }
        } else {
            $errors[] = 'Invalid operation ID given.';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        $this->_head('fix-data', $op ? $op['mission_name'] . ' (' . $op['id'] . ')' : '');

        $this->load->view('admin/events', [
            'errors' => $errors,
            'op' => $op,
            'items' => $op_events,
            'op_sides' => $op_sides,
            'op_commanders' => $op_commanders,
            'op_entities' => $op_entities,
            'entity_id' => $entity_id,
            'player' => $player,
            'op_sus_suicides' => $op_sus_suicides
        ]);

        $this->_foot();
    }

    public function edit_entity() {
        $this->load->model('operations');
        $this->load->model('additional_data');

        $errors = [];
        $action = $this->input->post('action');
        $operation_id = filter_var($this->input->post('operation_id'), FILTER_VALIDATE_INT);

        $op = false;
        if ($operation_id !== false) {
            $op = $this->operations->get_by_id($operation_id);

            if ($op) {
                if (intval($op['verified']) === 0) {
                    $entity_id = filter_var($this->input->post('entity_id'), FILTER_VALIDATE_INT);
                    if ($entity_id !== false) {
                        log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] entity (' . $op['id'] . ' - ' . $entity_id . ') action: ' . $action);
                        try {
                            if ($action === 'not-a-player') {
                                $err = $this->additional_data->update_op_entity($op['id'], $entity_id, [
                                    'player_id' => 0,
                                    'is_player' => 0
                                ]);
                                $errors = array_merge($errors, $err);

                                log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] entity (' . $op['id'] . ' - ' . $entity_id . ') update finished (errors: ' . count($errors) . ')');
                            } else {
                                $errors[] = 'Invalid action.';
                            }
                        } catch (Exception $e) {
                            $errors[] = $e->getMessage();
                        }
                    } else {
                        $errors[] = 'Invalid entity ID given.';
                    }
                } else {
                    $errors[] = 'Entities of a verified op can not be edited!';
                }
            } else {
                $errors[] = 'Unknown operation ID given.';
            }
        } else {
            $errors[] = 'Invalid operation ID given.';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        return $this->_ajax([
            'errors' => $errors,
            'action' => $action
        ]);
    }

    public function edit_event() {
        $this->load->model('operations');
        $this->load->model('additional_data');

        $errors = [];
        $action = $this->input->post('action');
        $operation_id = filter_var($this->input->post('operation_id'), FILTER_VALIDATE_INT);

        $op = false;
        if ($operation_id !== false) {
            $op = $this->operations->get_by_id($operation_id);
            if ($op) {
                if (intval($op['verified']) === 0) {
                    $event_id = filter_var($this->input->post('event_id'), FILTER_VALIDATE_INT);
                    if ($event_id !== false) {
                        log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] event (' . $op['id'] . ' - ' . $event_id . ') action: ' . $action);
                        try {
                            if ($action === 'delete-event') {
                                $err = $this->additional_data->delete_op_event($op['id'], $event_id);
                                $errors = array_merge($errors, $err);

                                log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] event (' . $op['id'] . ' - ' . $event_id . ') delete finished (errors: ' . count($errors) . ')');
                            } elseif ($action === 'edit-event') {
                                $new_attacker_id = $this->input->post('new_attacker_id');
                                if ($new_attacker_id !== null) {
                                    if ($new_attacker_id === '') {
                                        $new_attacker_id = null;
                                    }

                                    $err = $this->additional_data->edit_op_event_attacker($op['id'], $event_id, $new_attacker_id);
                                    $errors = array_merge($errors, $err);

                                    log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] event (' . $op['id'] . ' - ' . $event_id . ') edit-attacker (' . $new_attacker_id . ') finished (errors: ' . count($errors) . ')');
                                }

                                $new_weapon = $this->input->post('new_weapon');
                                if ($new_weapon !== null) {
                                    $err = $this->additional_data->update_op_event($op['id'], $event_id, ['weapon' => $new_weapon]);
                                    $errors = array_merge($errors, $err);

                                    log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] event (' . $op['id'] . ' - ' . $event_id . ') edit-weapon (' . $new_weapon . ') finished (errors: ' . count($errors) . ')');
                                }
                            } else {
                                $errors[] = 'Invalid action.';
                            }
                        } catch (Exception $e) {
                            $errors[] = $e->getMessage();
                        }
                    } else {
                        $errors[] = 'Invalid event ID given.';
                    }
                } else {
                    $errors[] = 'Events of a verified op can not be edited!';
                }
            } else {
                $errors[] = 'Unknown operation ID given.';
            }
        } else {
            $errors[] = 'Invalid operation ID given.';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        return $this->_ajax([
            'errors' => $errors,
            'action' => $action
        ]);
    }
}
