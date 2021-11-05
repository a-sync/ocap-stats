<?php defined('BASEPATH') or exit('*');

class Players extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function get_players($events_filter, $id = false)
    {
        if (is_array($events_filter) && count($events_filter) > 0) {
            $this->db->where_in('operations.event', $events_filter);
        } elseif ($events_filter !== false) {
            $this->db->where('operations.event !=', '');
        }

        if ($id) {
            if (!is_array($id)) {
                $id = [$id];
            }

            $this->db->where_in('players.id', $id);
        }

        $this->db->select(['players.name', 'players.id'])
            ->select_sum('entities.shots')
            ->select_sum('entities.hits')
            ->select_sum('entities.fhits')
            ->select_sum('entities.kills')
            ->select_sum('entities.fkills')
            ->select_sum('entities.vkills')
            ->select_sum('entities.deaths')
            ->select('COUNT(DISTINCT `entities`.`operation_id`) AS `attendance`')
            ->from('players')
            ->join('entities', 'entities.player_id = players.id')
            ->join('operations', 'operations.id = entities.operation_id')
            ->where('players.alias_of', 0)
            ->group_by('players.id')
            ->order_by('attendance DESC, kills DESC, deaths ASC, hits DESC, vkills DESC, shots ASC');

        $players = $this->db->get()->result_array();

        if (defined('ADJUST_HIT_DATA') && ADJUST_HIT_DATA >= 0) {
            // Get adjusted hit stats for players
            if (is_array($events_filter) && count($events_filter) > 0) {
                $this->db->where_in('operations.event', $events_filter);
            } elseif ($events_filter !== false) {
                $this->db->where('operations.event !=', '');
            }

            if ($id) {
                if (!is_array($id)) {
                    $id = [$id];
                }

                $this->db->where_in('players.id', $id);
            }

            $this->db->select(['players.id'])
                ->select_sum('entities.shots')
                ->select_sum('entities.hits')
                ->select_sum('entities.fhits')
                ->from('players')
                ->join('entities', 'entities.player_id = players.id AND entities.operation_id >= ' . ADJUST_HIT_DATA)
                ->join('operations', 'operations.id = entities.operation_id')
                ->where('players.alias_of', 0)
                ->group_by('players.id');

            $players_adjusted_stats = $this->db->get()->result_array();
            $players_adjusted_stats = array_column($players_adjusted_stats, null, 'id');

            foreach ($players as $i => $p) {
                if (isset($players_adjusted_stats[$p['id']])) {
                    $players[$i]['adj_shots'] = $players_adjusted_stats[$p['id']]['shots'];
                    $players[$i]['adj_hits'] = $players_adjusted_stats[$p['id']]['hits'];
                    $players[$i]['adj_fhits'] = $players_adjusted_stats[$p['id']]['fhits'];
                } else {
                    // Player only attended before hit events got recorded
                    $players[$i]['adj_shots'] = false;
                    $players[$i]['adj_hits'] = false;
                    $players[$i]['adj_fhits'] = false;
                }
            }
        } else {
            foreach ($players as $i => $p) {
                $players[$i]['adj_shots'] = $players[$i]['shots'];
                $players[$i]['adj_hits'] = $players[$i]['hits'];
                $players[$i]['adj_fhits'] = $players[$i]['fhits'];
            }
        }

        return $players;
    }

    public function get_by_id($id)
    {
        $re = $this->get_players(true, $id);

        if (count($re) === 0) {
            return false;
        } else {
            return $re[0];
        }
    }

    public function get_ops_by_id($id)
    {
        $this->db
            ->select([
                'entities.operation_id',
                'entities.id',
                'entities.player_id',
                'entities.group_name',
                'entities.name',
                'entities.role',
                'entities.side',
                'operations.mission_name',
                'operations.mission_duration',
                'operations.world_name',
                'operations.filename',
                'operations.event',
                'operations.start_time',
                'operations.end_winner'
            ])
            ->select_sum('shots')
            ->select_sum('hits')
            ->select_sum('fhits')
            ->select_sum('kills')
            ->select_sum('fkills')
            ->select_sum('vkills')
            ->select_sum('deaths')
            ->from('entities')
            ->where('entities.player_id', $id)
            ->join('players', 'players.id = entities.player_id')
            ->join('operations', 'operations.id = entities.operation_id')
            ->group_by('entities.id, entities.operation_id')
            ->order_by('entities.operation_id DESC, entities.id ASC');

        return $this->db
            ->get()
            ->result_array();
    }

    public function get_commanders($events_filter, $return_unsolvable = false)
    {
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
            'entities.id AS entity_id',
            'entities.player_id',
            'entities.side',
            'entities.group_name',
            'entities.role',
            'operations.id AS operation_id',
            'operations.end_winner',
            "SUBSTRING_INDEX(entities.role, '@', 1) AS role_name"
        ])
            ->from('players')
            ->join('entities', 'entities.player_id = players.id')
            ->join('operations', 'operations.id = entities.operation_id')
            // ->where('operations.end_winner !=', '')
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

        $leads = $this->db->get()->result_array();

        $op_leads = [];
        $matching_sides = [];
        $ops_teams_with_unsolvable_hq = [];
        foreach ($leads as $l) {
            $op_id = $l['operation_id'];
            $side = $l['side'];

            if (!isset($matching_sides[$side])) {
                $matching_sides[$side] = true;
            }

            if (!isset($op_leads[$op_id])) {
                $op_leads[$op_id] = [];
            }

            if (!isset($ops_teams_with_unsolvable_hq[$op_id][$side])) {
                if (!isset($op_leads[$op_id][$side])) {
                    $op_leads[$op_id][$side] = $l;
                } elseif ($op_leads[$op_id][$side]['player_id'] !== $l['player_id']) {
                    if (
                        $op_leads[$op_id][$side]['group_name'] === $l['group_name'] &&
                        $op_leads[$op_id][$side]['role_name'] === $l['role_name']
                    ) {
                        $ops_teams_with_unsolvable_hq[$op_id][$side] = [$op_leads[$op_id][$side], $l];
                        unset($op_leads[$op_id][$side]);
                    } else {
                        $op_leads[$op_id][$side] = $this->_return_commander($op_leads[$op_id][$side], $l);
                    }
                }
            } else {
                $ops_teams_with_unsolvable_hq[$op_id][$side][] = $l;
            }
        }

        if ($return_unsolvable) {
            return $ops_teams_with_unsolvable_hq;
        }
        $ops_teams_with_unsolvable_hq = null;

        $commanders = [];
        foreach ($op_leads as $ol) {
            foreach ($ol as $l) {
                $id = $l['player_id'];
                if (!isset($commanders[$id])) {
                    $commanders[$id] = [
                        'name' => $l['name'],
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

    public function get_aliases_by_id($id = false)
    {
        $this->db->select(['players.name', 'players.id', 'players.alias_of'])
            ->from('players')
            ->where('players.alias_of', $id);

        return $this->db
            ->get()
            ->result_array();
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

    public function get_roles_by_id($id)
    {
        $this->db
            ->select([
                "SUBSTRING_INDEX(entities.role, '@', 1) AS role_name",
                'COUNT(entities.role) AS total_count'
            ])
            ->select_sum("CASE WHEN side = 'WEST' THEN 1 ELSE 0 END", 'west_count')
            ->select_sum("CASE WHEN side = 'EAST' THEN 1 ELSE 0 END", 'east_count')
            ->select_sum("CASE WHEN side = 'GUER' THEN 1 ELSE 0 END", 'guer_count')
            ->select_sum("CASE WHEN side = 'CIV' THEN 1 ELSE 0 END", 'civ_count')
            ->select_sum('shots')
            ->select_sum('hits')
            ->select_sum('fhits')
            ->select_sum('kills')
            ->select_sum('fkills')
            ->select_sum('vkills')
            ->select_sum('deaths')
            ->from('entities')
            ->where('entities.player_id', $id)
            ->where('entities.role !=', '')
            ->group_by('role_name')
            ->order_by('total_count DESC, kills DESC, deaths ASC');

        $roles = $this->db->get()->result_array();

        if (defined('ADJUST_HIT_DATA') && ADJUST_HIT_DATA >= 0) {
            // Get adjusted hit stats for roles
            $this->db
                ->select([
                    "SUBSTRING_INDEX(entities.role, '@', 1) AS role_name",
                    'COUNT(entities.role) AS total_count',
                ])
                ->select_sum('shots')
                ->select_sum('hits')
                ->select_sum('fhits')
                ->from('entities')
                ->where('entities.player_id', $id)
                ->where('entities.role !=', '')
                ->where('entities.operation_id >=', ADJUST_HIT_DATA)
                ->group_by('role_name');

            $roles_adjusted_stats = $this->db->get()->result_array();
            $roles_adjusted_stats = array_column($roles_adjusted_stats, null, 'role_name');

            foreach ($roles as $i => $r) {
                if (isset($roles_adjusted_stats[$r['role_name']])) {
                    $roles[$i]['adj_shots'] = $roles_adjusted_stats[$r['role_name']]['shots'];
                    $roles[$i]['adj_hits'] = $roles_adjusted_stats[$r['role_name']]['hits'];
                    $roles[$i]['adj_fhits'] = $roles_adjusted_stats[$r['role_name']]['fhits'];
                } else {
                    // Player only attended before hit events got recorded
                    $roles[$i]['adj_shots'] = false;
                    $roles[$i]['adj_hits'] = false;
                    $roles[$i]['adj_fhits'] = false;
                }
            }
        } else {
            foreach ($roles as $i => $r) {
                $roles[$i]['adj_shots'] = $roles[$i]['shots'];
                $roles[$i]['adj_hits'] = $roles[$i]['hits'];
                $roles[$i]['adj_fhits'] = $roles[$i]['fhits'];
            }
        }

        return $roles;
    }

    private function get_opponents_by_id($id, $type)
    {
        $this->db
            ->select(['players.id', 'players.name'])
            ->select_sum("CASE WHEN events.event = 'hit' THEN 1 ELSE 0 END", 'hits')
            ->select_sum("CASE WHEN events.event = 'hit' AND attacker.side = victim.side THEN 1 ELSE 0 END", 'fhits')
            ->select_avg("CASE WHEN events.event = 'hit' THEN events.distance ELSE NULL END", 'hit_dist_avg')
            ->select_sum("CASE WHEN events.event = 'killed' THEN 1 ELSE 0 END", 'kills')
            ->select_sum("CASE WHEN events.event = 'killed' AND attacker.side = victim.side THEN 1 ELSE 0 END", 'fkills')
            ->select_avg("CASE WHEN events.event = 'killed' THEN events.distance ELSE NULL END", 'kill_dist_avg')
            ->from('events')
            ->join('entities AS attacker', 'attacker.id = events.attacker_id AND attacker.operation_id = events.operation_id')
            ->join('entities AS victim', 'victim.id = events.victim_id AND victim.operation_id = events.operation_id')
            ->group_by('players.id')
            ->order_by('kills DESC, hits DESC, fkills ASC, fhits ASC, players.name ASC');

        if ($type === 'attackers') {
            $this->db
                ->join('players', 'players.id = attacker.player_id')
                ->where('victim.player_id', $id);
        } elseif ($type === 'victims') {
            $this->db
                ->join('players', 'players.id = victim.player_id')
                ->where('attacker.player_id', $id);
        }

        return $this->db->get()->result_array();
    }

    public function get_attackers_by_id($id)
    {
        return $this->get_opponents_by_id($id, 'attackers');
    }

    public function get_victims_by_id($id)
    {
        return $this->get_opponents_by_id($id, 'victims');
    }
}
