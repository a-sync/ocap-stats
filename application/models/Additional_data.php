<?php defined('BASEPATH') or exit('*');

class Additional_data extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    /**
     * @return array of players<-entities<-operations data
     * all the players with entities matching hq group/role name ordered 
     * by rank
     */
    private function _get_commander_prospects($events_filter, $op_ids = false)
    {
        if ($op_ids !== false) {
            $this->db->where_in('operations.id', $op_ids);
        }

        if (is_array($events_filter) && count($events_filter) > 0) {
            $this->db->where_in('operations.event', $events_filter);
        } elseif ($events_filter !== false) {
            $this->db->where('operations.event !=', '');
        }

        $hq_group_names = $this->config->item('hq_group_names');
        if (count($hq_group_names) > 0) {
            $this->db->where_in('entities.group_name', $hq_group_names)
                ->order_by("FIELD(entities.group_name, '" . implode("', '", $hq_group_names) . "')");
        }

        $this->db->select([
            'players.name',
            'entities.operation_id',
            'entities.id AS entity_id',
            'entities.name AS entity_name',
            'entities.player_id',
            'entities.side',
            'entities.group_name',
            'entities.role',
            'operations.end_winner',
            "SUBSTRING_INDEX(entities.role, '@', 1) AS role_name"
        ])
            ->from('players')
            ->join('entities', 'entities.player_id = players.id')
            ->join('operations', 'operations.id = entities.operation_id')
            ->where('players.alias_of', 0);

        $hq_role_names = $this->config->item('hq_role_names');
        if (count($hq_role_names) > 0) {
            $order_by_role_name = 'CASE ';
            $this->db->group_start();
            foreach ($hq_role_names as $i => $rn) {
                $this->db->or_where('entities.role LIKE', $rn . '%@%');
                $order_by_role_name .= 'WHEN role_name LIKE ' . $this->db->escape($rn . '%') . ' THEN ' . strval($i + 1) . ' ';
            }
            $this->db->or_where('entities.role', '');
            $this->db->group_end();
            $order_by_role_name .= 'ELSE ' . strval(count($hq_role_names) + 1) . ' END';
            $this->db->order_by($order_by_role_name, 'ASC', false);
        }

        $this->db->order_by('entities.id ASC');

        return $this->db->get()->result_array();
    }

    private function _get_verified_commanders($events_filter, $op_ids = false)
    {
        if ($op_ids !== false) {
            $this->db->where_in('operations.id', $op_ids);
        }

        if (is_array($events_filter) && count($events_filter) > 0) {
            $this->db->where_in('operations.event', $events_filter);
        } elseif ($events_filter !== false) {
            $this->db->where('operations.event !=', '');
        }

        $this->db->select([
            'players.name',
            'entities.operation_id',
            'entities.id AS entity_id',
            'entities.name AS entity_name',
            'entities.player_id',
            'entities.side',
            'entities.group_name',
            'entities.role',
            'operations.end_winner',
            "SUBSTRING_INDEX(entities.role, '@', 1) AS role_name"
        ])
            ->from('entities_additional_data AS ead')
            ->join('entities', 'entities.id = ead.entity_id AND entities.operation_id = ead.operation_id')
            ->join('players', 'players.id = entities.player_id')
            ->join('operations', 'operations.id = entities.operation_id')
            ->where('ead.hq', 1)
            ->where('players.alias_of', 0)
            ->where('operations.event !=', '')
            ->order_by('entity_id ASC');

        $leads = $this->db->get()->result_array();

        $dupes = [];
        $verified_op_leads = [];
        foreach ($leads as $l) {
            $op_id = $l['operation_id'];
            $side = $l['side'];
            if (!isset($verified_op_leads[$op_id][$side])) {
                $verified_op_leads[$op_id][$side] = $l;
            } else {
                $dupes[] = implode('/', [$l['operation_id'], $l['entity_id'], $l['side']]);
            }
        }

        if (count($dupes) > 0) {
            log_message('error', 'Duplicate hq entities found: ' . implode(', ', $dupes));
        }

        return $verified_op_leads;
    }

    public function get_commanders($events_filter, $op_ids = false, $return_ops_data = false)
    {
        if ($op_ids !== false) {
            if (!is_array($op_ids)) {
                $op_ids = [$op_ids];
            }
        }

        $leads = $this->_get_commander_prospects($events_filter, $op_ids);

        $op_leads = [];
        $ambiguous_op_leads = [];
        foreach ($leads as $l) {
            $op_id = $l['operation_id'];
            $side = $l['side'];

            if (!isset($ambiguous_op_leads[$op_id][$side])) {
                if (!isset($op_leads[$op_id][$side])) {
                    $op_leads[$op_id][$side] = $l;
                } elseif ($op_leads[$op_id][$side]['player_id'] !== $l['player_id']) {
                    if (
                        $op_leads[$op_id][$side]['group_name'] === $l['group_name'] &&
                        $op_leads[$op_id][$side]['role_name'] === $l['role_name']
                    ) {
                        /**
                         * if the highest ranking entity considered for hq is 
                         * not unique the commander can not be determined 
                         * unambiguously
                         * (we bail out here and _return_commander() will 
                         * never have to compare entities by ID numbers)
                         */
                        $ambiguous_op_leads[$op_id][$side] = [$op_leads[$op_id][$side], $l];
                        unset($op_leads[$op_id][$side]);
                        if (count($op_leads[$op_id]) === 0) {
                            unset($op_leads[$op_id]);
                        }
                    } else {
                        /**
                         * compare the entity currently set as hq to the next
                         * entity considered for hq
                         */
                        $op_leads[$op_id][$side] = $this->_return_commander($op_leads[$op_id][$side], $l);
                    }
                }
            } else {
                $ambiguous_op_leads[$op_id][$side][] = $l;
            }
        }

        $verified_op_leads = $this->_get_verified_commanders($events_filter, $op_ids);

        $unambiguous_op_leads = $op_leads;
        foreach ($verified_op_leads as $op_id => $ol) {
            // if (isset($op_leads[$op_id])) {
            //     $op_leads[$op_id] = [];
            // }
            foreach ($ol as $side => $l) {
                $op_leads[$op_id][$side] = $l;
            }
        }

        if ($return_ops_data === true) {
            return [
                'unambiguous' => $unambiguous_op_leads,
                'ambiguous' => $ambiguous_op_leads,
                'verified' => $verified_op_leads,
                'resolved' => $op_leads
            ];
        }
        $unambiguous_op_leads = null;
        $ambiguous_op_leads = null;
        $verified_op_leads = null;

        $matching_sides = [];
        foreach ($op_leads as $ol) {
            foreach ($ol as $side => $l)
                if (!isset($matching_sides[$side])) {
                    $matching_sides[$side] = true;
                }
        }

        $commanders = [];
        foreach ($op_leads as $ol) {
            foreach ($ol as $l) {
                $id = $l['player_id'];
                if (!isset($commanders[$id])) {
                    $commanders[$id] = [
                        'name' => $l['name'],
                        'entity_name' => $l['entity_name'],
                        'player_id' => $id,
                        'win_total' => 0,
                        'loss_total' => 0
                    ];
                    foreach ($matching_sides as $s => $v) {
                        $commanders[$id][$s] = [
                            'win' => 0,
                            'loss' => 0
                        ];
                    }
                }

                if ($l['end_winner'] !== '') {
                    if ($l['end_winner'] === $l['side']) {
                        $commanders[$id][$l['side']]['win']++;
                        $commanders[$id]['win_total']++;
                    } else {
                        $commanders[$id][$l['side']]['loss']++;
                        $commanders[$id]['loss_total']++;
                    }
                }
            }
        }

        $wins = array_column($commanders, 'win_total');
        $losses = array_column($commanders, 'loss_total');

        array_multisort($wins, SORT_DESC, SORT_NUMERIC, $losses, SORT_ASC, SORT_NUMERIC, $commanders);

        return $commanders;
    }

    /**
     * Compare entities by group_name first, by role_name second and by ID 
     * as a last resort and return the highest ranking one.
     */
    private function _return_commander($entity_1, $entity_2)
    {
        if ($entity_1['group_name'] !== $entity_2['group_name']) {
            $hq_group_names = $this->config->item('hq_group_names');
            if (count($hq_group_names) > 0) {
                $e1_group_index = array_search($entity_1['group_name'], $hq_group_names);
                $e2_group_index = array_search($entity_2['group_name'], $hq_group_names);

                if ($e1_group_index !== $e2_group_index) {
                    if ($e2_group_index === false) return $entity_1;
                    else if ($e1_group_index === false) return $entity_2;
                    else if ($e2_group_index > $e1_group_index) {
                        return $entity_1;
                    } else if ($e1_group_index > $e2_group_index) {
                        return $entity_2;
                    }
                }
            }
        }

        if ($entity_1['role_name'] !== $entity_2['role_name']) {
            $hq_role_names = $this->config->item('hq_role_names');
            if (count($hq_role_names) > 0) {
                $e1_role_index = $this->_array_search_prefix($entity_1['role_name'], $hq_role_names);
                $e2_role_index = $this->_array_search_prefix($entity_2['role_name'], $hq_role_names);

                if ($e1_role_index !== $e2_role_index) {
                    if ($e2_role_index === false) return $entity_1;
                    else if ($e1_role_index === false) return $entity_2;
                    else if ($e2_role_index > $e1_role_index) {
                        return $entity_1;
                    } else {
                        return $entity_2;
                    }
                }
            }
        }

        if ($entity_1['entity_id'] <= $entity_2['entity_id']) {
            return $entity_1;
        } else {
            return $entity_2;
        }
    }

    private function _array_search_prefix($prefix, array $haystack)
    {
        foreach ($haystack as $i => $item) {
            if (0 === stripos($item, $prefix)) {
                return $i;
            }
        }

        return false;
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

    public function add_new_player($name)
    {
        $errors = [];
        $player_id = 0;
        $alias_of = 0;

        $p = $this->db
            ->select(['id', 'alias_of'])
            ->from('players')
            ->where('name', $name)
            ->get()
            ->result_array();

        if (count($p) > 0) {
            $errors[] = 'Player already exists.';
            $player_id = $p[0]['id'];
            $alias_of = $p[0]['alias_of'];
        } else {
            if ($this->db->insert('players', ['name' => $name]) === false) {
                $errors[] = 'Failed to create new player.';
            } else {
                $player_id = $this->db->insert_id();
            }
        }

        return [
            'errors' => $errors,
            'player_id' => $player_id,
            'alias_of' => $alias_of
        ];
    }

    public function get_ops_with_missing_data($id = false, $op_ids_with_unambiguous_hq = [])
    {
        if ($id !== false) {
            if (!is_array($id)) {
                $id = [$id];
            }

            $this->db->where_in('operations.id', $id);
        }

        if (count($op_ids_with_unambiguous_hq) === 0) {
            $op_ids_with_unambiguous_hq = [0];
        }

        $this->db
            ->select([
                'operations.id',
                'operations.mission_name',
                'operations.filename',
                'operations.date',
                'operations.tag',
                'operations.event',
                'UNIX_TIMESTAMP(operations.updated) AS updated',
                'operations.mission_author',
                'operations.start_time',
                'operations.end_winner',
                'operations.end_message',
                'oad.mission_author AS ad_mission_author',
                'oad.start_time AS ad_start_time',
                'oad.end_winner AS ad_end_winner',
                'oad.end_message AS ad_end_message',
                'oad.verified'
            ])
            ->from('operations')
            ->join('ops_additional_data AS oad', 'oad.operation_id = operations.id', 'LEFT')
            ->where('operations.event !=', '')
            ->where('IFNULL(oad.verified, 0) !=', 1)
            ->group_start()
            ->or_where('operations.mission_author', '')
            ->or_where('operations.start_time LIKE', '%00:00:00')
            ->or_where('operations.end_winner', '')
            ->or_where_not_in('operations.id', $op_ids_with_unambiguous_hq)
            ->group_end()
            ->order_by('operations.id', 'DESC');

        return $this->db
            ->get()
            ->result_array();
    }

    public function get_op_sides($id)
    {
        $this->db
            ->select('side')
            ->from('entities')
            ->where('entities.operation_id', $id)
            ->where('entities.is_player', 1)
            ->group_by('side');

        $re = $this->db
            ->get()
            ->result_array();

        return array_column($re, 'side');
    }
}
