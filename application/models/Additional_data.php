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
            "SUBSTRING_INDEX(entities.role, '@', 1) AS role_name",
            'entities.invalid',
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
            "SUBSTRING_INDEX(entities.role, '@', 1) AS role_name",
            'entities.invalid',
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
                if ((!is_null($l['cmd']) && intval($l['cmd']) === 0) || intval($l['invalid']) === 1) {
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

    public function get_player_cmd_stats($player_id) {
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

    public function get_ops_to_fix_data($verified = false, $missing_data = false, $op_ids_with_unambiguous_cmd = [])
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
                '(SELECT COUNT(DISTINCT ents.side) FROM entities AS ents WHERE ents.operation_id = operations.id AND ents.is_player = 1) AS sides_total'
            ])
            ->from('operations')
            ->where('operations.event !=', '')
            ->where('IFNULL(operations.verified, 0) =', $verified ? 1 : 0)
            ->order_by('operations.id DESC');

        if ($missing_data) {
            $this->db->group_start();

            $this->db->or_where('operations.mission_author', '')
                ->or_where('operations.start_time LIKE', '%00:00:00')
                ->or_where('operations.end_winner', '');

            if (count($op_ids_with_unambiguous_cmd) !== 0) {
                $this->db->or_where_not_in('operations.id', $op_ids_with_unambiguous_cmd);
            }

            $this->db->group_end();
        }

        return $this->db
            ->get()
            ->result_array();
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

    public function get_op_player_entities($id)
    {
        $this->db
            ->select([
                'entities.id AS entity_id',
                'entities.name AS entity_name',
                'entities.side',
                'entities.group_name',
                'entities.role',
                'entities.invalid'
            ])
            ->from('entities')
            ->join('players', 'players.id = entities.player_id')
            ->where('entities.operation_id', $id)
            ->where('entities.is_player', 1)
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
}
