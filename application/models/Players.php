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
            ->select_sum('entities.distance_traveled')
            ->select('SUM((entities.last_frame_num - entities.start_frame_num) * operations.capture_delay) AS seconds_in_game')
            ->select('COUNT(DISTINCT entities.operation_id) AS attendance')
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

    public function get_alias_of_by_id($id) {
        $re = $this->db
            ->select('alias_of')
            ->from('players')
            ->where('id', $id)
            ->get()
            ->result_array();

        if (count($re) === 0) {
            return null;
        } else {
            return $re[0]['alias_of'];
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
                'entities.shots',
                'entities.hits',
                'entities.fhits',
                'entities.kills',
                'entities.fkills',
                'entities.vkills',
                'entities.deaths',
                'entities.distance_traveled',
                'entities.cmd',
                '((entities.last_frame_num - entities.start_frame_num) * operations.capture_delay) AS seconds_in_game',
                'operations.mission_name',
                'operations.mission_duration',
                'operations.world_name',
                'operations.filename',
                'operations.tag',
                'operations.event',
                'operations.date',
                'operations.start_time',
                'operations.end_winner',
                'operations.end_message',
                '(SELECT COUNT(DISTINCT ents.player_id) FROM entities AS ents WHERE ents.operation_id = operations.id) AS players_total'
            ])
            ->from('operations')
            ->join('entities', 'entities.operation_id = operations.id', 'RIGHT')
            ->join('players', 'players.id = entities.player_id')
            ->where('entities.player_id', $id)
            ->group_by('entities.id, entities.operation_id')
            ->order_by('entities.operation_id DESC, entities.id ASC');

        return $this->db
            ->get()
            ->result_array();
    }

    public function get_roles_by_id($id)
    {
        $this->db
            ->select([
                "TRIM(SUBSTRING_INDEX(entities.role, '@', 1)) AS role_name",
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
            ->select_sum('distance_traveled')
            ->select('SUM((entities.last_frame_num - entities.start_frame_num) * operations.capture_delay) AS seconds_in_game')
            ->from('entities')
            ->join('operations', 'operations.id = entities.operation_id')
            ->where('entities.player_id', $id)
            ->where('entities.role !=', '')
            ->group_by('role_name')
            ->order_by('total_count DESC, kills DESC, deaths ASC');

        $roles = $this->db->get()->result_array();

        if (defined('ADJUST_HIT_DATA') && ADJUST_HIT_DATA >= 0) {
            // Get adjusted hit stats for roles
            $this->db
                ->select([
                    "TRIM(SUBSTRING_INDEX(entities.role, '@', 1)) AS role_name",
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
        $opponents = [];
        $entity_types = ['unit', 'vehicle'];
        foreach ($entity_types as $et) {
            $this->db
                ->select_sum("CASE WHEN events.event = 'hit' THEN 1 ELSE 0 END", 'hits')
                ->select_sum("CASE WHEN events.event = 'hit' AND attacker.side = victim.side THEN 1 ELSE 0 END", 'fhits')
                ->select_avg("CASE WHEN events.event = 'hit' THEN events.distance ELSE NULL END", 'avg_hit_dist')
                ->select_sum("CASE WHEN events.event = 'killed' THEN 1 ELSE 0 END", 'kills')
                ->select_sum("CASE WHEN events.event = 'killed' AND attacker.side = victim.side THEN 1 ELSE 0 END", 'fkills')
                ->select_avg("CASE WHEN events.event = 'killed' THEN events.distance ELSE NULL END", 'avg_kill_dist')
                ->from('events')
                ->join('entities AS attacker', 'attacker.id = events.attacker_id AND attacker.operation_id = events.operation_id', $type === 'attackers' ? 'LEFT' : 'INNER')
                ->join('entities AS victim', 'victim.id = events.victim_id AND victim.operation_id = events.operation_id', $type !== 'attackers' ? 'LEFT' : 'INNER')
                ->where('victim.player_id != attacker.player_id')
                ->where_in('events.event', ['hit', 'killed'])
                ->order_by('kills DESC, hits DESC, fkills ASC, fhits ASC, name ASC');

            if ($type === 'attackers') {
                $this->db
                    ->select(['attacker.type', 'attacker.class'])
                    ->where('attacker.type', $et)
                    ->where('victim.player_id', $id);

                if ($et === 'unit') {
                    $this->db
                        ->select(['players.id as player_id', 'players.name'])
                        ->join('players', 'players.id = attacker.player_id')
                        ->group_by('players.id');
                } else { // vehicle
                    $this->db
                        ->select('attacker.name')
                        ->group_by('attacker.name, attacker.class');
                }
            } else { // victims
                $this->db
                    ->select(['victim.type', 'victim.class'])
                    ->where('victim.type', $et)
                    ->where('attacker.player_id', $id);

                if ($et === 'unit') {
                    $this->db
                        ->select(['players.id as player_id', 'players.name'])
                        ->join('players', 'players.id = victim.player_id')
                        ->group_by('players.id');
                } else { // vehicle
                    $this->db
                        ->select('victim.name')
                        ->group_by('victim.name, victim.class');
                }
            }

            $res = $this->db->get()->result_array();
            if ($res && count($res) > 0) {
                $opponents = array_merge($opponents, $res);
            }
        }

        $kills = array_column($opponents, 'kills');
        $hits = array_column($opponents, 'hits');
        $fkills = array_column($opponents, 'fkills');
        $fhits = array_column($opponents, 'fhits');
        $names = array_column($opponents, 'name');

        array_multisort($kills, SORT_DESC, SORT_NUMERIC, $hits, SORT_DESC, SORT_NUMERIC, $fkills, SORT_ASC, SORT_NUMERIC, $fhits, SORT_ASC, SORT_NUMERIC, $names, SORT_ASC, SORT_STRING, $opponents);

        return $opponents;
    }

    public function get_attackers_by_id($id)
    {
        return $this->get_opponents_by_id($id, 'attackers');
    }

    public function get_victims_by_id($id)
    {
        return $this->get_opponents_by_id($id, 'victims');
    }

    public function get_weapons_by_id($id)
    {
        $this->db
            ->select('events.weapon')
            ->select_sum("CASE WHEN events.event = 'hit' THEN 1 ELSE 0 END", 'hits')
            ->select_sum("CASE WHEN events.event = 'hit' AND attacker.side = victim.side THEN 1 ELSE 0 END", 'fhits')
            ->select_avg("CASE WHEN events.event = 'hit' THEN events.distance ELSE NULL END", 'avg_hit_dist')
            ->select_sum("CASE WHEN events.event = 'killed' THEN 1 ELSE 0 END", 'kills')
            ->select_sum("CASE WHEN events.event = 'killed' AND attacker.side = victim.side THEN 1 ELSE 0 END", 'fkills')
            ->select_avg("CASE WHEN events.event = 'killed' THEN events.distance ELSE NULL END", 'avg_kill_dist')
            ->select('COUNT(DISTINCT events.operation_id) AS ops')
            ->from('events')
            ->join('entities AS attacker', 'attacker.id = events.attacker_id AND attacker.operation_id = events.operation_id')
            ->join('entities AS victim', 'victim.id = events.victim_id AND victim.operation_id = events.operation_id')
            ->where('attacker.player_id', $id)
            ->where('victim.player_id != attacker.player_id')
            ->where_in('events.event', ['hit', 'killed'])
            ->where('events.weapon !=', '')
            ->group_by('events.weapon')
            ->order_by('kills DESC, hits DESC, fkills ASC, fhits ASC, events.weapon ASC');

        return $this->db->get()->result_array();
    }
}
