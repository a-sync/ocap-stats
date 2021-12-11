<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Data extends CI_Controller
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
        return $this->load->view('foot');
    }

    public function index()
    {
        $this->add_alias();
    }

    public function add_alias()
    {
        $this->load->helper(['cache', 'date']);
        $this->load->model('additional_data');

        $errors = [];
        $alias_of = $this->input->get('alias_of');
        $add_alias = $this->input->post('add_alias');
        $add_new_player = $this->input->post('add_new_player');
        $new_player_name = $this->input->post('new_player_name');
        $player_id = $this->input->post('player_id');
        $aliases = $this->input->post('aliases');
        if (!is_array($aliases)) {
            $aliases = [];
        }

        if (!is_null($alias_of)) {
            $current_aliases = $this->additional_data->get_aliases($alias_of);

            return $this->_ajax(array_column($current_aliases, 'id'));
        } elseif ($add_alias) {
            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] updating aliases of ' . $player_id . ' (' . implode(', ', $aliases) . ')');

            if (filter_var($player_id, FILTER_VALIDATE_INT)) {
                $player_exists = $this->additional_data->player_exists($player_id);

                if ($player_exists) {
                    $aliases_valid = $this->additional_data->validate_aliases($player_id, $aliases);
                    if ($aliases_valid) {
                        $err = $this->additional_data->update_aliases($player_id, $aliases);
                        $errors = array_merge($errors, $err);
                    } else {
                        $errors[] = 'Invalid aliases selected!';
                    }
                } else {
                    $errors[] = 'Unknown player ID given.';
                }
            } else {
                $errors[] = 'Invalid player ID given!';
            }
        } elseif ($add_new_player) {
            log_message('error', 'EVENT --> [' . $this->adminUser['name'] . '] adding player ' . $new_player_name);

            if (!is_null($new_player_name) && $new_player_name !== '') {
                $re = $this->additional_data->add_new_player($new_player_name);
                $errors = array_merge($errors, $re['errors']);
                if ($re['player_id'] > 0) {
                    $player_id = $re['alias_of'] > 0 ? strval($re['alias_of']) : strval($re['player_id']);
                }
            } else {
                $errors[] = 'Player name can not be empty!';
            }
        }

        $players = $this->additional_data->get_players_names();

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

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
            'last_cache_update' => $last_cache_update
        ]);

        $this->_foot();
    }

    private function _ajax($data)
    {
        if (!$this->input->is_ajax_request()) {
            return show_error(400);
        }

        $json_flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, $json_flags));
    }

    public function fix_data($tab = 'missing')
    {
        $this->load->model('additional_data');

        $errors = [];
        $ops_leads_all = $this->additional_data->get_commanders(true, false, true);
        $op_ids_with_resolved_hq = array_keys(array_filter($ops_leads_all['resolved'], function ($v) {
            return count($v) > 1;
        }));

        $ops = $this->additional_data->get_ops_with_missing_data(false, $op_ids_with_resolved_hq, ($tab === 'unverified' ? true : false));

        $this->_head('fix-data', 'Fix missing OP data');

        $this->load->view('admin/fix-data', [
            'items' => $ops,
            'hq_unambiguous' => $ops_leads_all['unambiguous'],
            'hq_verified' => $ops_leads_all['verified'],
            'errors' => $errors,
            'tab' => $tab
        ]);

        $this->_foot();
    }

    public function override_op_data($op_id = null)
    {
        $this->load->model('operations');
        $this->load->model('additional_data');
        $this->load->helper('date');

        $errors = [];
        $op = false;
        $op_sides = [];
        $op_commanders_data = [];
        $op_ad = ['verified' => 0]; //debug
        if (filter_var($op_id, FILTER_VALIDATE_INT) || filter_var($op_id, FILTER_VALIDATE_INT) === 0) {
            $op = $this->operations->get_by_id($op_id);

            if ($op) {
                $op_sides = $this->additional_data->get_op_sides($op['id']);
                $op_commanders_data = $this->additional_data->get_commanders(true, $op['id'], true);
            } else {
                $errors[] = 'Unknown operation ID given.';
            }
        } else {
            $errors[] = 'Invalid operation ID given.';
        }

        if (count($errors) > 0) {
            log_message('error', implode('; ', $errors));
        }

        $this->_head('fix-data', $op ? $op['mission_name'] . ' (' . $op['id'] . ') additional data' : '');

        $this->load->view('admin/override-op-data', [
            'errors' => $errors,
            'op' => $op,
            'op_sides' => $op_sides,
            'hq_resolved' => $op ? element($op['id'], $op_commanders_data['resolved'], []) : [],
            'hq_verified' => $op ? element($op['id'], $op_commanders_data['verified'], []) : [],
            'hq_unambiguous' => $op ? element($op['id'], $op_commanders_data['unambiguous'], []) : [],
            'hq_ambiguous' => $op ? element($op['id'], $op_commanders_data['ambiguous'], []) : [],
            'op_ad' => $op_ad
        ]);

        $this->_foot();
    }
}
