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
        $this->load->model('additional_data');
        $players = $this->additional_data->get_players_names();

        $errors = ['💩 TODO'];
        $this->_head('add-alias', 'Add player alias');

        $this->load->view('admin/add-alias', [
            'players' => $players,
            'errors' => $errors
        ]);

        $this->_foot();
    }

    public function fix_op_data()
    {
        // $this->load->model('players');
        // $this->load->model('operations');
        // $hq_unsolvable = $this->players->get_commanders(true, true);
        // $no_winner = $this->operations->get_ops(true, false, true);

        $errors = ['💩 TODO'];
        $this->_head('fix-op-data', 'Fill in missing op data');

        $this->load->view('admin/fix-op-data', [
            // 'ops' => $ops,
            // 'op_units' => $op_units,
            'errors' => $errors
        ]);

        $this->_foot();
    }
}
