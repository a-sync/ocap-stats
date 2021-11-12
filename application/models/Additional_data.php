<?php defined('BASEPATH') or exit('*');

class Additional_data extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function get_players_names($aliases = false)
    {
        $this->db->select(['players.name', 'players.id', 'players.alias_of'])
            ->from('players')
            ->where('players.alias_of' . ($aliases ? ' !=' : ''), 0)
            ->order_by('players.name', 'ASC');

        return $this->db
            ->get()
            ->result_array();
    }
}
