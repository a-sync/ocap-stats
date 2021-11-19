<?php defined('BASEPATH') or exit('*');

class Additional_data extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function get_players_names($aliases = null)
    {
        $this->db->select(['players.name', 'players.id', 'players.alias_of'])
            ->from('players')
            ->order_by('players.name', 'ASC');

        if (!is_null($aliases)) {
            $this->db->where('players.alias_of' . ($aliases ? ' !=' : ''), 0);
        }

        return $this->db
            ->get()
            ->result_array();
    }

    public function get_aliases($player_id)
    {
        $this->db->select(['players.name', 'players.id'])
            ->from('players')
            ->where('players.alias_of', $player_id);

        return $this->db
            ->get()
            ->result_array();
    }

    public function player_exists($player_id)
    {
        $re = $this->db
            ->select('id')
            ->from('players')
            ->where('alias_of', 0)
            ->where('id', $player_id)
            ->get()
            ->result_array();

        if (count($re) === 0) {
            return false;
        } else {
            return true;
        }
    }

    public function validate_aliases($player_id, $alias_ids)
    {
        if (count($alias_ids) === 0) {
            return true;
        }

        if (in_array($player_id, $alias_ids)) {
            return false;
        }

        $re = $this->db
            ->select('id')
            ->from('players')
            ->where('id !=', $player_id)
            ->where_in('id', $alias_ids)
            ->get()
            ->result_array();

        $diff = array_diff($alias_ids, array_column($re, 'id'));

        if (count($diff) === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function update_aliases($player_id, $alias_ids)
    {
        $errors = [];

        $current_aliases = $this->get_aliases($player_id);
        $removed_aliases = array_diff(array_column($current_aliases, 'id', 'name'), $alias_ids);

        if (count($removed_aliases) > 0) {
            $this->db->where_in('id', $removed_aliases);
            if (!$this->db->update('players', ['alias_of' => 0])) {
                $errors[] = 'Failed to remove deselected aliases. (' . implode(', ', $removed_aliases) . ')';
            }

            if (count($errors) === 0) {
                foreach ($removed_aliases as $rname => $rid) {
                    $this->db->where('player_id', $player_id);
                    $this->db->where('name', $rname);
                    if (!$this->db->update('entities', ['player_id' => $rid])) {
                        $errors[] = 'Failed to restore player IDs of entity. (' . $rid . ' => ' . html_escape($rname) . ')';
                    }
                }
            }
        }

        if (count($alias_ids) > 0) {
            $this->db->where_in('id', $alias_ids);
            if (!$this->db->update('players', ['alias_of' => $player_id])) {
                $errors[] = 'Failed to add selected aliases. (' . implode(', ', $alias_ids) . ')';
            }

            $this->db->where_in('player_id', $alias_ids);
            if (!$this->db->update('entities', ['player_id' => $player_id])) {
                $errors[] = 'Failed to update player IDs of entities. (' . implode(', ', $alias_ids) . ')';
            }
        }

        return $errors;
    }
}
