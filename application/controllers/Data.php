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
                        $errors[] = 'Invalid aliases selected.';
                    }
                } else {
                    $errors[] = 'Unknown player ID given.';
                }
            } else {
                $errors[] = 'Invalid player ID given.';
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

    public function fix_data()
    {
        // $this->load->model('players');
        // $this->load->model('operations');
        // $hq_unsolvable = $this->players->get_commanders(true, true);
        // $no_winner = $this->operations->get_ops(true, false, true);

        $errors = ['💩 TODO'];
        $this->_head('fix-data', 'Fix op data');

        $this->load->view('admin/fix-data', [
            // 'ops' => $ops,
            // 'op_units' => $op_units,
            'errors' => $errors
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
