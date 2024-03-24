<?php defined('BASEPATH') or exit('*');

class Additional_data extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    /**
     * @return array of players<-entities<-operations data
     * all the players with entities matching cmd group/role name ordered 
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

        $cmd_group_names = $this->config->item('cmd_group_names');
        if (count($cmd_group_names) > 0) {
            $this->db->where_in('entities.group_name', $cmd_group_names)
                ->order_by("FIELD(entities.group_name, '" . implode("', '", $cmd_group_names) . "')");
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
            "TRIM(SUBSTRING_INDEX(entities.role, '@', 1)) AS role_name",
            'entities.cmd',
            'operations.end_winner'
        ])
            ->from('players')
            ->join('entities', 'entities.player_id = players.id')
            ->join('operations', 'operations.id = entities.operation_id');

        $cmd_role_names = $this->config->item('cmd_role_names');
        if (count($cmd_role_names) > 0) {
            $order_by_role_name = 'CASE ';
            $this->db->group_start();
            foreach ($cmd_role_names as $i => $rn) {
                $this->db->or_where('entities.role LIKE', $rn . '%@%');
                $order_by_role_name .= 'WHEN role_name LIKE ' . $this->db->escape($rn . '%') . ' THEN ' . strval($i + 1) . ' ';
            }
            $this->db->or_where('entities.role', '');
            $this->db->group_end();
            $order_by_role_name .= 'ELSE ' . strval(count($cmd_role_names) + 1) . ' END';
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
            "TRIM(SUBSTRING_INDEX(entities.role, '@', 1)) AS role_name",
            'entities.cmd',
            'operations.end_winner'
        ])
            ->from('entities')
            ->join('players', 'players.id = entities.player_id')
            ->join('operations', 'operations.id = entities.operation_id')
            ->where('entities.cmd', 1)
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
                $dupes[] = implode('/', [$l['operation_id'], $l['side'], $l['entity_id'], $l['entity_name']]);
            }
        }

        if (count($dupes) > 0) {
            log_message('error', 'Duplicate commander entities found: ' . implode(', ', $dupes));
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

        $prospects = $this->_get_commander_prospects($events_filter, $op_ids);

        $op_leads = [];
        $ambiguous_op_leads = [];
        foreach ($prospects as $l) {
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
                         * if the highest ranking entity considered for cmd is 
                         * not unique the commander can not be determined 
                         * unambiguously
                         * (we bail out here to make sure _return_commander() will 
                         * never have to compare entities by ID numbers)
                         */
                        $ambiguous_op_leads[$op_id][$side] = [$op_leads[$op_id][$side], $l];
                        unset($op_leads[$op_id][$side]);
                        if (count($op_leads[$op_id]) === 0) {
                            unset($op_leads[$op_id]);
                        }
                    } else {
                        /**
                         * compare the entity currently set as cmd to the next
                         * entity considered for cmd
                         */
                        $op_leads[$op_id][$side] = $this->_return_commander($op_leads[$op_id][$side], $l);
                    }
                }
            } else {
                $ambiguous_op_leads[$op_id][$side][] = $l;
            }
        }
        $prospects = null;
        $unambiguous_op_leads = $op_leads;

        $verified_op_leads = $this->_get_verified_commanders($events_filter, $op_ids);
        foreach ($verified_op_leads as $op_id => $ol) {
            foreach ($ol as $side => $l) {
                $op_leads[$op_id][$side] = $l;
            }
        }

        $matching_sides = [];
        foreach ($op_leads as $op_id => $ol) {
            foreach ($ol as $side => $l)
                if (!is_null($l['cmd']) && intval($l['cmd']) === 0) {
                    unset($op_leads[$op_id][$side]);
                } elseif (!isset($matching_sides[$side])) {
                    $matching_sides[$side] = true;
                }
        }

        if ($return_ops_data === true) {
            return [
                'ambiguous' => $ambiguous_op_leads,
                'unambiguous' => $unambiguous_op_leads,
                'verified' => $verified_op_leads,
                'resolved' => $op_leads
            ];
        }
        $ambiguous_op_leads = null;
        $unambiguous_op_leads = null;
        $verified_op_leads = null;

        $commanders = [];
        foreach ($op_leads as $ol) {
            $commanded_sides = array_keys($ol);
            foreach ($ol as $l) {
                $id = $l['player_id'];
                if (!isset($commanders[$id])) {
                    $commanders[$id] = [
                        'name' => $l['name'],
                        'entity_name' => $l['entity_name'],
                        'player_id' => $id,
                        'win_total' => 0,
                        'loss_total' => 0,
                        'draw_total' => 0
                    ];
                    foreach ($matching_sides as $s => $v) {
                        $commanders[$id][$s] = [
                            'win' => 0,
                            'loss' => 0,
                            'draw' => 0
                        ];
                    }
                }

                if ($l['end_winner'] !== '') {
                    $winner_sides = explode('/', $l['end_winner']);
                    if (in_array($l['side'], $winner_sides)) {
                        // leader of a winning side
                        $commanders[$id][$l['side']]['win']++;
                        $commanders[$id]['win_total']++;
                    } else {
                        if (count(array_intersect($winner_sides, $commanded_sides)) > 0) {
                            // other commander is the leader of a winning side
                            $commanders[$id][$l['side']]['loss']++;
                            $commanders[$id]['loss_total']++;
                        } else {
                            // no commander on winning side
                            $commanders[$id][$l['side']]['draw']++;
                            $commanders[$id]['draw_total']++;
                        }
                    }
                } else {
                    $commanders[$id][$l['side']]['draw']++;
                    $commanders[$id]['draw_total']++;
                }
            }
        }

        $wins = array_column($commanders, 'win_total');
        $losses = array_column($commanders, 'loss_total');
        //$draws = array_column($commanders, 'draw_total');
        $totals = array_map(function ($c) {
            return $c['win_total'] + $c['loss_total'] + $c['draw_total'];
        }, $commanders);

        array_multisort($totals, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $losses, SORT_ASC, SORT_NUMERIC, $commanders);

        return $commanders;
    }

    public function get_player_cmd_stats($player_id)
    {
        $op_commanders_data = $this->get_commanders(true, false, true);
        $matching_sides = [];

        $commanded_ops = [];
        $commanded_sides = [];
        foreach ($op_commanders_data['resolved'] as $op_id => $op_cmds) {
            $sides = [];
            foreach ($op_cmds as $s => $e) {
                $sides[] = $s;
                if ($e['player_id'] === $player_id) {
                    $commanded_ops[$op_id] = $op_cmds;
                    $commanded_sides[$op_id] = $s;
                }
            }

            if (isset($commanded_ops[$op_id])) {
                foreach ($sides as $s) {
                    if (!isset($matching_sides[$s])) {
                        $matching_sides[$s] = true;
                    }
                }
            }
        }
        $op_commanders_data = null;

        $rivals = [];
        foreach ($commanded_ops as $op_id => $op_cmds) {
            foreach ($op_cmds as $s => $r) {
                $rid = $r['player_id'];

                if ($rid !== $player_id) {
                    if (!isset($rivals[$rid])) {
                        $rivals[$rid] = [
                            'name' => $r['name'],
                            'player_id' => $rid,
                            'win_total' => 0,
                            'loss_total' => 0,
                            'draw_total' => 0
                        ];
                        foreach ($matching_sides as $s => $v) {
                            $rivals[$rid][$s] = [
                                'win' => 0,
                                'loss' => 0,
                                'draw' => 0
                            ];
                        }
                    }

                    if ($r['end_winner'] !== '') {
                        $winner_sides = explode('/', $r['end_winner']);
                        if (in_array($r['side'], $winner_sides)) {
                            if (!in_array($commanded_sides[$op_id], $winner_sides)) {
                                // rival won and player did not
                                $rivals[$rid][$r['side']]['loss']++;
                                $rivals[$rid]['loss_total']++;
                            }
                        } else {
                            if (in_array($commanded_sides[$op_id], $winner_sides)) {
                                // player won and rival did not
                                $rivals[$rid][$r['side']]['win']++;
                                $rivals[$rid]['win_total']++;
                            } else {
                                // third side won
                                // Note: counting a draw automatically with more
                                // then two commanders is skipped since we can
                                // not determine alliances
                                if (count($op_cmds) < 3) {
                                    $rivals[$rid][$r['side']]['draw']++;
                                    $rivals[$rid]['draw_total']++;
                                }
                            }
                        }
                    } else {
                        // Note: alliances can not be resolved without end_winner
                        if (count($op_cmds) < 3) {
                            $rivals[$rid][$r['side']]['draw']++;
                            $rivals[$rid]['draw_total']++;
                        }
                    }
                }
            }
        }

        $rivals = array_filter($rivals, function ($r) {
            return boolval($r['win_total'] + $r['loss_total'] + $r['draw_total']);
        });
        $rivals = array_values($rivals);

        $wins = array_column($rivals, 'win_total');
        $losses = array_column($rivals, 'loss_total');
        //$draws = array_column($rivals, 'draw_total');
        $totals = array_map(function ($c) {
            return $c['win_total'] + $c['loss_total'] + $c['draw_total'];
        }, $rivals);

        array_multisort($totals, SORT_DESC, SORT_NUMERIC, $wins, SORT_DESC, SORT_NUMERIC, $losses, SORT_ASC, SORT_NUMERIC, $rivals);

        return [
            'rivals' => $rivals,
            'commanded_ops' => $commanded_ops
        ];
    }

    /**
     * Compare entities by group_name first, by role_name second and by ID 
     * as a last resort and return the highest ranking one.
     */
    private function _return_commander($entity_1, $entity_2)
    {
        if ($entity_1['group_name'] !== $entity_2['group_name']) {
            $cmd_group_names = array_map('strtolower', $this->config->item('cmd_group_names'));
            if (count($cmd_group_names) > 0) {
                $e1_group_index = array_search(strtolower($entity_1['group_name']), $cmd_group_names);
                $e2_group_index = array_search(strtolower($entity_2['group_name']), $cmd_group_names);

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
            $cmd_role_names = $this->config->item('cmd_role_names');
            if (count($cmd_role_names) > 0) {
                $e1_role_index = $this->_array_search_prefix($entity_1['role_name'], $cmd_role_names);
                $e2_role_index = $this->_array_search_prefix($entity_2['role_name'], $cmd_role_names);

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
        if ($prefix === '') {
            return false;
        }

        foreach ($haystack as $i => $item) {
            if (0 === stripos($item, $prefix)) {
                return $i;
            }
        }

        return false;
    }

    public function get_players_names($aliases = null)
    {
        $this->db->select(['name', 'id', 'alias_of', 'uid'])
            ->from('players')
            ->order_by('name ASC');

        if (!is_null($aliases)) {
            $this->db->where('alias_of' . ($aliases ? ' !=' : ''), 0);
        }

        return $this->db
            ->get()
            ->result_array();
    }

    public function get_aliases($player_id)
    {
        $this->db->select(['name', 'id'])
            ->from('players')
            ->where('alias_of', $player_id);

        return $this->db
            ->get()
            ->result_array();
    }

    private function validate_aliases($player_id, $alias_ids)
    {
        $player = $this->db
            ->select('id')
            ->from('players')
            ->where('alias_of', 0)
            ->where('id', $player_id)
            ->get()
            ->result_array();

        if (count($player) === 0) {
            return ['Unknown player ID given!'];
        }

        if (count($alias_ids) === 0) {
            return [];
        }

        if (in_array($player_id, $alias_ids)) {
            return ['Self ID in alias list.'];
        }

        $errors = [];

        $alias_players = $this->db
            ->select(['id', 'alias_of', 'uid'])
            ->from('players')
            ->where('id !=', $player_id)
            ->where_in('id', $alias_ids)
            ->get()
            ->result_array();

        $not_found = array_diff($alias_ids, array_column($alias_players, 'id'));
        if (count($not_found) > 0) {
            $errors[] = ['Invalid IDs in alias list.'];
        }

        foreach ($alias_players as $ap) {
            if ($ap['alias_of'] && $ap['alias_of'] !== $player_id) {
                $errors[] = 'Player #' . $ap['id'] . ' is alias of player #' . $ap['alias_of'] . ' already!';
            }
            if ($ap['uid'] !== null) {
                $errors[] = 'Player #' . $ap['id'] . ' has UID!';
            }
        }

        return $errors;
    }

    public function update_aliases($player_id, $alias_ids)
    {
        $errors = $this->validate_aliases($player_id, $alias_ids);
        if (count($errors) > 0) {
            return $errors;
        }

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
                    $this->db->where('uid', null);
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

            $this->db->where_in('alias_of', $alias_ids);
            if (!$this->db->update('players', ['alias_of' => $player_id])) {
                $errors[] = 'Failed to update inherited aliases. (' . implode(', ', $alias_ids) . ')';
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
        $name = substr($name, 0, 255);
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
                $errors[] = 'Failed to create new player (' . $name . ')';
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

    public function get_ops_to_fix_data($verified = false, $op_ids_with_unambiguous_cmd = [])
    {
        $this->db
            ->select([
                'operations.id',
                'operations.world_name',
                'operations.mission_name',
                'operations.mission_duration',
                'operations.filename',
                'operations.date',
                'operations.tag',
                'operations.event',
                'UNIX_TIMESTAMP(operations.updated) AS updated',
                'operations.mission_author',
                'operations.start_time',
                'operations.end_winner',
                'operations.end_message',
                '(SELECT COUNT(DISTINCT ents.side) FROM entities AS ents WHERE ents.operation_id = operations.id AND ents.is_player = 1) AS sides_total',
                'IFNULL(dupe_deaths.multi_death_players_count, 0) AS multi_death_players_count',
                'IFNULL(dupe_deaths.extra_deaths_count, 0) AS extra_deaths_count',
                "(SELECT COUNT(evs.id) FROM events AS evs INNER JOIN entities AS ents ON evs.attacker_aid = ents.aid AND ents.is_player = 1 WHERE evs.operation_id = operations.id AND evs.attacker_id = evs.victim_id AND evs.event = 'killed') AS suicides_total",
                'IFNULL(sus_suicides.sus_suicides_count, 0) AS sus_suicides_count'
            ])
            ->from('operations')
            ->join("(
                SELECT 
                    player_deaths.id, 
                    COUNT(player_deaths.player_id) AS multi_death_players_count, 
                    SUM(player_deaths.extra_deaths) AS extra_deaths_count 
                FROM (
                    SELECT o.id, e.player_id, SUM(e.deaths) - 1 AS extra_deaths 
                    FROM operations AS o 
                    INNER JOIN entities AS e ON o.id = e.operation_id AND e.player_id != 0 
                    GROUP BY o.id, e.player_id 
                    HAVING SUM(e.deaths) > 1 
                    ) AS player_deaths 
                GROUP BY player_deaths.id
                ) AS dupe_deaths",
                'dupe_deaths.id = operations.id', 'LEFT')
            ->join("(
                SELECT sus_suicide_events.operation_id, COUNT(sus_suicide_events.operation_id) AS sus_suicides_count
                FROM (
                    SELECT e.operation_id, e.victim_aid
                    FROM events AS e
                    INNER JOIN entities AS victim ON victim.aid = e.victim_aid
                    LEFT JOIN (
                        SELECT ee.operation_id, ee.victim_id, frame
                        FROM events AS ee
                        WHERE ee.victim_id = ee.attacker_id AND ee.victim_id IS NOT NULL AND ee.event = 'hit'
                        GROUP BY ee.operation_id, ee.victim_id
                        ) AS hits ON hits.operation_id = e.operation_id AND hits.victim_id = e.victim_id AND hits.frame = e.frame
                    WHERE e.victim_aid = e.attacker_aid AND hits.victim_id IS NULL AND victim.is_player = 1 AND e.event = 'killed'
                    ) AS sus_suicide_events
                GROUP BY sus_suicide_events.operation_id
                ) AS sus_suicides",
                'sus_suicides.operation_id = operations.id', 'LEFT')
            ->where('operations.event !=', '')
            ->where('IFNULL(operations.verified, 0) =', $verified ? 1 : 0)
            ->order_by('operations.id DESC');

        if (count($op_ids_with_unambiguous_cmd) !== 0) {
            $this->db->group_start()
                ->or_where('operations.mission_author', '')
                ->or_where('operations.start_time LIKE', '%00:00:00')
                ->or_where('operations.end_winner', '')
                ->or_where_not_in('operations.id', $op_ids_with_unambiguous_cmd)
                ->or_where('multi_death_players_count >', 0)
                ->or_where('sus_suicides_count >', 0)
                ->group_end();
        }

        return $this->db
            ->get()
            ->result_array();
    }

    public function get_op_sus_suicides($id)
    {
        $this->db->select('e.id')
            ->from('events AS e')
            ->join('entities AS victim', 'victim.aid = e.victim_aid')
            ->join("(SELECT ee.operation_id, ee.victim_id, frame
                FROM events AS ee
                WHERE ee.victim_id = ee.attacker_id AND ee.victim_id IS NOT NULL AND ee.event = 'hit'
                GROUP BY ee.operation_id, ee.victim_id
            ) AS hits", 'hits.operation_id = e.operation_id AND hits.victim_id = e.victim_id AND hits.frame = e.frame', 'LEFT')
            ->where("e.victim_aid = e.attacker_aid AND hits.victim_id IS NULL AND victim.is_player = 1 AND e.event = 'killed'")
            ->where('e.operation_id', $id);

        return array_column($this->db->get()->result_array(), 'id');
    }

    public function get_op_sides($id)
    {
        $this->db
            ->select([
                'entities.side',
                'COUNT(DISTINCT entities.player_id) AS players_total'
            ])
            ->from('entities')
            ->where('entities.operation_id', $id)
            ->where('entities.is_player', 1)
            ->group_by('entities.side')
            ->order_by('entities.id ASC');

        $players = $this->db
            ->get()
            ->result_array();

        $op_sides = array_column($players, 'players_total', 'side');

        $this->db
            ->select('entities.side')
            ->from('entities')
            ->where('entities.operation_id', $id)
            ->where('entities.is_player', 0)
            ->group_by('entities.side')
            ->order_by('entities.id ASC');

        $others = $this->db
            ->get()
            ->result_array();

        foreach ($others as $r) {
            if (!isset($op_sides[$r['side']])) {
                $op_sides[$r['side']] = 0;
            }
        }

        return $op_sides;
    }

    public function get_op_entities($id)
    {
        $this->db
            ->select([
                'entities.id',
                'entities.name',
                'entities.side',
                'entities.is_player'
            ])
            ->from('entities')
            ->where('entities.operation_id', $id)
            ->order_by('entities.is_player DESC, entities.id ASC');

        return $this->db->get()->result_array();
    }

    public function get_op_player_entities_simple($id)
    {
        $this->db
            ->select([
                'entities.id AS entity_id',
                'entities.name AS entity_name',
                'entities.side',
                'entities.group_name',
                'entities.role'
            ])
            ->from('entities')
            ->join('players', 'players.id = entities.player_id')
            ->where('entities.operation_id', $id)
            ->where('entities.is_player', 1)
            ->order_by('entities.id ASC');

        return $this->db->get()->result_array();
    }

    public function get_op_player_entities($id)
    {
        $this->db
            ->select([
                'entities.id',
                'entities.player_id',
                'entities.group_name',
                'entities.is_player',
                'entities.name',
                'entities.role',
                'entities.side',
                'entities.type',
                'entities.class',
                'entities.distance_traveled',
                '(entities.start_frame_num * operations.capture_delay) AS join_time_seconds',
                '(entities.last_frame_num - entities.start_frame_num) * operations.capture_delay AS seconds_in_game',
                'players.name AS player_name'
            ])
            ->select_sum('shots')
            ->select_sum('hits')
            ->select_sum('fhits')
            ->select_sum('kills')
            ->select_sum('fkills')
            ->select_sum('vkills')
            ->select_sum('deaths')
            ->from('entities')
            ->join('operations', 'operations.id = entities.operation_id')
            ->join('players', 'players.id = entities.player_id AND entities.operation_id = ' . $id, 'LEFT')
            ->where('entities.operation_id', $id)
            ->where('entities.is_player', 1)
            ->group_by('entities.id')
            ->order_by('entities.id ASC');

        return $this->db->get()->result_array();
    }

    public function update_op($data)
    {
        $errors = [];

        $this->db->where('id', $data['id']);
        if (!$this->db->update('operations', $data)) {
            $errors[] = 'Failed to update op data. (' . $data['id'] . ')';
        }

        return $errors;
    }

    public function update_op_commanders($op_id, $entities)
    {
        $errors = [];

        $this->db
            ->where('operation_id', $op_id)
            ->update('entities', ['cmd' => null]);

        if (count($entities) > 0) {
            $this->db->where('operation_id', $op_id);
            if (false === $this->db->update_batch('entities', $entities, 'id')) {
                $errors[] = 'Failed to update op commanders data. (' . $op_id . ')';
            }
        }

        return $errors;
    }

    public function get_first_ops_of_players($ids)
    {
        $this->db
            ->select([
                'players.id',
                'MIN(entities.operation_id) AS operation_id'
            ])
            ->from('players')
            ->join('entities', 'entities.player_id = players.id')
            ->where_in('players.id', $ids)
            ->group_by('players.id');

        return array_column($this->db->get()->result_array(), 'operation_id', 'id');
    }

    public function get_new_names($ops_limit = 6)
    {
        $this->db
            ->select('id')
            ->from('operations')
            ->where('event !=', '')
            ->order_by('id', 'DESC')
            ->limit($ops_limit);
        $operations = $this->db->get()->result_array();
        $operation_ids = array_unique(array_column($operations, 'id'));

        $this->db
            ->select('operations.id AS operation_id, operations.event, operations.mission_name, operations.start_time, players.id AS player_id, entities.name AS entity_name')
            ->from('operations')
            ->where('operations.event !=', '')
            ->join('entities', 'operations.id = entities.operation_id')
            ->join('players', 'entities.player_id = players.id')
            ->where_in('operations.id', $operation_ids)
            ->order_by('operations.id', 'DESC');
        $operations_players = $this->db->get()->result_array();
        $player_ids = array_unique(array_column($operations_players, 'player_id'));

        $players_first_op = $this->get_first_ops_of_players($player_ids);

        $new_players = [];
        foreach ($operations_players as $player) {
            if (!isset($new_players[$player['player_id']]) && isset($players_first_op[$player['player_id']]) && $players_first_op[$player['player_id']] === $player['operation_id']) {
                $new_players[$player['player_id']] = $player;
            }
        }

        $grouped_results = [];
        foreach ($new_players as $np) {
            $opid = $np['operation_id'];

            if (!isset($grouped_results[$opid])) {
                $grouped_results[$opid] = [
                    'operation_id' => $opid,
                    'event' => $np['event'],
                    'mission_name' => $np['mission_name'],
                    'start_time' => $np['start_time'],
                    'players' => []
                ];
            }

            $grouped_results[$opid]['players'][$np['player_id']] = $np['entity_name'];
        }

        return $grouped_results;
    }

    public function calculate_entities_sus_factor($op_entities) {
        $player_entities = [];
        foreach ($op_entities as $i => $ent) {
            if (!isset($player_entities[$ent['player_id']])) $player_entities[$ent['player_id']] = [];
            if (intval($ent['player_id']) !== 0) $player_entities[$ent['player_id']][] = $ent['id'];
        }
        $op_entities = array_map(function($ent) use ($player_entities) {
            $ent['player_entities'] = $player_entities[$ent['player_id']];
            return $ent;
        }, $op_entities);

        function get_iqr_stat_value($stat_values) {
            sort($stat_values);
            $q1 = $stat_values[floor(count($stat_values) * 0.25)];
            $q3 = $stat_values[floor(count($stat_values) * 0.75)];
            return $q3 - $q1;
        }

        function get_stddev_stat_value($stat_values) {
            $mean = array_sum($stat_values) / count($stat_values);
            $squared_deviations = array_map(function($value) use ($mean) {
                return pow($value - $mean, 2);
            }, $stat_values);
            $variance = array_sum($squared_deviations) / count($stat_values);
            return sqrt($variance);
        }

        function points_for_deviation($value, $average, $iqr, $stddev, $weight = 1) {
            $deviation_from_iqr = abs($value - $average) - $iqr / 2;
            $normalized_deviation_iqr = $iqr ? $deviation_from_iqr / $iqr : $deviation_from_iqr;
            $deviation_from_stddev = $stddev ? abs($value - $average) / $stddev : abs($value - $average);
            $points = ($normalized_deviation_iqr + $deviation_from_stddev) * $weight;
            return $points > 0 ? $points : 0;
        }

        function points_for_low_deviation($value, $average, $iqr, $stddev, $weight = 1) {
            $deviation_from_iqr = max(0, $average - $value - $iqr / 2);
            $normalized_deviation_iqr = $iqr ? $deviation_from_iqr / $iqr : $deviation_from_iqr;
            $deviation_from_stddev = max(0, $average - $value - 2 * $stddev);
            $points = ($normalized_deviation_iqr + $deviation_from_stddev) * $weight;
            return $points;
        }

        $seconds_in_game = array_column($op_entities, 'seconds_in_game');
        $seconds_in_game_avg = array_sum($seconds_in_game) / count($seconds_in_game);
        $distance_traveled = array_column($op_entities, 'distance_traveled');
        $distance_traveled_avg = array_sum($distance_traveled) / count($distance_traveled);
        $deaths = array_column($op_entities, 'deaths');
        $deaths_avg = array_sum($deaths) / count($deaths);
        $player_entities = array_map(function ($pe) {
            return count($pe);
        }, array_column($op_entities, 'player_entities'));
        $player_entities_avg = array_sum($player_entities) / count($player_entities);

        $seconds_in_game_iqr = get_iqr_stat_value($seconds_in_game);
        $seconds_in_game_stddev = get_stddev_stat_value($seconds_in_game);
        $distance_traveled_iqr = get_iqr_stat_value($distance_traveled);
        $distance_traveled_stddev = get_stddev_stat_value($distance_traveled);
        $deaths_iqr = get_iqr_stat_value($deaths);
        $deaths_stddev = get_stddev_stat_value($deaths);
        $player_entities_iqr = get_iqr_stat_value($player_entities);
        $player_entities_stddev = get_stddev_stat_value($player_entities);

        foreach ($op_entities as $i => $ent) {
            $sus_factor = 0;
            if ($ent['is_player']) {
                $sus_factor = points_for_low_deviation($ent['seconds_in_game'], $seconds_in_game_avg, $seconds_in_game_iqr, $seconds_in_game_stddev, 2) + points_for_low_deviation($ent['distance_traveled'], $distance_traveled_avg, $distance_traveled_iqr, $distance_traveled_stddev) + points_for_deviation($ent['deaths'], $deaths_avg, $deaths_iqr, $deaths_stddev, 50) + points_for_deviation(count($ent['player_entities']), $player_entities_avg, $player_entities_iqr, $player_entities_stddev, 50);
            }
            $op_entities[$i]['sus_factor'] = $sus_factor;
        }

        return $op_entities;
    }

    public function update_op_entity($op_id, $entity_id, $data)
    {
        $errors = [];

        $this->db->where('operation_id', $op_id);
        $this->db->where('id', $entity_id);
        if (!$this->db->update('entities', $data)) {
            $errors[] = 'Failed to update entity data. (' . $op_id . ' - ' . $entity_id . ')';
        }

        return $errors;
    }

    private function get_op_event($op_id, $event_id)
    {
        $this->db
            ->select([
                'events.event',
                'events.victim_id',
                'events.attacker_id',
                'victim.id AS victim_id',
                'victim.type AS victim_type',
                'victim.side AS victim_side',
                'victim.hits AS victim_hits',
                'victim.kills AS victim_kills',
                'victim.fhits AS victim_fhits',
                'victim.fkills AS victim_fkills',
                'victim.vkills AS victim_vkills',
                'victim.deaths AS victim_deaths',
                'attacker.id AS attacker_id',
                'attacker.side AS attacker_side',
                'attacker.hits AS attacker_hits',
                'attacker.kills AS attacker_kills',
                'attacker.fhits AS attacker_fhits',
                'attacker.fkills AS attacker_fkills',
                'attacker.vkills AS attacker_vkills',
                'attacker.deaths AS attacker_deaths'
            ])
            ->from('events')
            ->join('entities AS victim', 'victim.id = events.victim_id AND victim.operation_id = ' . $op_id, 'LEFT')
            ->join('entities AS attacker', 'attacker.id = events.attacker_id AND attacker.operation_id = ' . $op_id, 'LEFT')
            ->where('events.operation_id', $op_id)
            ->where('events.id', $event_id)
            ->order_by('events.frame ASC');

        $re = $this->db->get()->result_array();

        if (count($re) === 0) {
            return false;
        } else {
            return $re[0];
        }
    }

    private function get_op_entities_events($op_id, $entity_ids, $events = ['killed', '_dead']) {
        $this->db
            ->select([
                'events.id',
                'events.event',
                'events.victim_id',
                'events.attacker_id',
            ])
            ->from('events')
            ->where('events.operation_id', $op_id)
            ->where_in('event', $events)
            ->group_start()
            ->where_in('attacker_id', $entity_ids)
            ->or_where_in('victim_id', $entity_ids)
            ->group_end();

        return $this->db->get()->result_array();
    }

    public function delete_op_event($op_id, $event_id)
    {
        $errors = [];
        $event = $this->get_op_event($op_id, $event_id);
        if ($event) {
            $ev = $event['event'];

            if (in_array($ev, ['hit', 'killed', '_dead'])) {
                if ($ev === 'hit' && $event['attacker_id'] !== null && $event['attacker_id'] !== $event['victim_id']) {
                    $hits_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['hits' => $event['attacker_hits'] - 1]);
                    $fhits_upd_errors = [];

                    if ($event['attacker_side'] === $event['victim_side']) {
                        $fhits_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['fhits' => $event['attacker_fhits'] - 1]);
                    }

                    $errors = array_merge($errors, $hits_upd_errors, $fhits_upd_errors);
                } elseif (in_array($ev,['killed', '_dead'])) {
                    if ($event['attacker_id'] !== null && $ev === 'killed' && $event['attacker_id'] !== $event['victim_id']) {
                        if ($event['victim_type'] === 'unit') {
                            $kills_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['kills' => $event['attacker_kills'] - 1]);
                            $fkills_upd_errors = [];

                            if ($event['attacker_side'] === $event['victim_side']) {
                                $fkills_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['fkills' => $event['attacker_fkills'] - 1]);
                            }

                            $errors = array_merge($errors, $kills_upd_errors, $fkills_upd_errors);
                        } else {
                            $vkills_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['vkills' => $event['attacker_vkills'] - 1]);

                            $errors = array_merge($errors, $vkills_upd_errors);
                        }
                    }

                    if ($event['victim_id'] !== null) {
                        $knd_events = $this->get_op_entities_events($op_id, [$event['victim_id']], ['killed', '_dead']);

                        $killed = 0;
                        $_dead = 0;
                        foreach ($knd_events as $e) {
                            if (intval($e['id']) !== $event_id && $e['victim_id'] === $event['victim_id']) {
                                if ($e['event'] === 'killed') {
                                    $killed++;
                                } elseif ($e['event'] === '_dead') {
                                    $_dead++;
                                }
                            }
                        }

                        $deaths = max($killed, $_dead);
                        $deaths_upd_errors = $this->update_op_entity($op_id, $event['victim_id'], ['deaths' => $deaths]);

                        $errors = array_merge($errors, $deaths_upd_errors);
                    }
                }
            }

            if (count($errors) === 0) {
                if (!$this->db->delete('events', ['operation_id' => $op_id, 'id' => $event_id])) {
                    $errors[] = 'Failed to delete event.';
                }
            }
        } else {
            $errors[] = 'Event not found.';
        }

        return $errors;
    }

    private function get_op_entity($op_id, $entity_id) {
        $this->db
            ->select([
                'entities.id',
                'entities.aid',
                'entities.side',
                'entities.hits',
                'entities.kills',
                'entities.fhits',
                'entities.fkills',
                'entities.vkills',
                'entities.deaths'
            ])
            ->from('entities')
            ->where('entities.operation_id', $op_id)
            ->where('entities.id', $entity_id);

        $re = $this->db->get()->result_array();

        if (count($re) === 0) {
            return false;
        } else {
            return $re[0];
        }
    }

    public function update_op_event($op_id, $event_id, $data)
    {
        $errors = [];

        $this->db->where('operation_id', $op_id);
        $this->db->where('id', $event_id);
        if (!$this->db->update('events', $data)) {
            $errors[] = 'Failed to update events data. (' . $op_id . ' - ' . $event_id . ')';
        }

        return $errors;
    }

    public function edit_op_event_attacker($op_id, $event_id, $new_attacker_id)
    {
        $errors = [];
        $event = $this->get_op_event($op_id, $event_id);
        if ($event) {
            $ev = $event['event'];
            if (in_array($ev, ['hit', 'killed'])) {
                if ($event['attacker_id'] !== $new_attacker_id) {
                    $new_attacker = is_numeric($new_attacker_id) ? $this->get_op_entity($op_id, $new_attacker_id) : false;

                    if ($new_attacker_id === null || $new_attacker) {
                        if ($event['attacker_id'] !== null && $event['attacker_id'] !== $event['victim_id']) {
                            if ($ev === 'hit') {
                                $hits_upd_errors = [];
                                $fhits_upd_errors = [];

                                if ($event['attacker_side'] === $event['victim_side']) {
                                    $fhits_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['fhits' => $event['attacker_fhits'] - 1]);
                                } else {
                                    $hits_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['hits' => $event['attacker_hits'] - 1]);
                                }

                                $errors = array_merge($errors, $hits_upd_errors, $fhits_upd_errors);
                            } elseif ($ev === 'killed') {
                                if ($event['victim_type'] === 'unit') {
                                    $kills_upd_errors = [];
                                    $fkills_upd_errors = [];

                                    if ($event['attacker_side'] === $event['victim_side']) {
                                        $fkills_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['fkills' => $event['attacker_fkills'] - 1]);
                                    } else {
                                        $kills_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['kills' => $event['attacker_kills'] - 1]);
                                    }

                                    $errors = array_merge($errors, $kills_upd_errors, $fkills_upd_errors);
                                } else {
                                    $vkills_upd_errors = $this->update_op_entity($op_id, $event['attacker_id'], ['vkills' => $event['attacker_vkills'] - 1]);

                                    $errors = array_merge($errors, $vkills_upd_errors);
                                }
                            }
                        }

                        if ($new_attacker && $new_attacker_id !== $event['victim_id']) {
                            if ($ev === 'hit') {
                                $new_hits_upd_errors = [];
                                $new_fhits_upd_errors = [];

                                if ($new_attacker['side'] === $event['victim_side']) {
                                    $new_fhits_upd_errors = $this->update_op_entity($op_id, $new_attacker_id, ['fhits' => $new_attacker['fhits'] + 1]);
                                } else {
                                    $new_hits_upd_errors = $this->update_op_entity($op_id, $new_attacker_id, ['hits' => $new_attacker['hits'] + 1]);
                                }

                                $errors = array_merge($errors, $new_hits_upd_errors, $new_fhits_upd_errors);
                            } elseif ($ev === 'killed') {
                                if ($event['victim_type'] === 'unit') {
                                    $new_kills_upd_errors = [];
                                    $new_fkills_upd_errors = [];

                                    if ($new_attacker['side'] === $event['victim_side']) {
                                        $new_fkills_upd_errors = $this->update_op_entity($op_id, $new_attacker_id, ['fkills' => $new_attacker['fkills'] + 1]);
                                    } else {
                                        $new_kills_upd_errors = $this->update_op_entity($op_id, $new_attacker_id, ['kills' => $new_attacker['kills'] + 1]);
                                    }

                                    $errors = array_merge($errors, $new_kills_upd_errors, $new_fkills_upd_errors);
                                } else {
                                    $new_vkills_upd_errors = $this->update_op_entity($op_id, $new_attacker_id, ['vkills' => $new_attacker['vkills'] + 1]);

                                    $errors = array_merge($errors, $new_vkills_upd_errors);
                                }
                            }
                        }

                        $new_attacker_id = $new_attacker ? $new_attacker['id'] : null;
                        $new_attacker_aid = $new_attacker ? $new_attacker['aid'] : null;

                        $err = $this->update_op_event($op_id, $event_id, [
                            'attacker_id' => $new_attacker_id,
                            'attacker_aid' => $new_attacker_aid
                        ]);

                        $errors = array_merge($errors, $err);
                    } else {
                        $errors[] = 'New attacker entity not found.';
                    }
                }
            } else {
                $errors[] = 'Event type not supported.';
            }
        } else {
            $errors[] = 'Event not found.';
        }

        return $errors;
    }
}
