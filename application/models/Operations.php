<?php defined('BASEPATH') or exit('*');

class Operations extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function exists($id)
    {
        $re = $this->db
            ->select('id')
            ->from('operations')
            ->where('id', $id)
            ->get()
            ->result_array();

        if (count($re) === 0) {
            return false;
        } else {
            return true;
        }
    }

    public function get_all_ids_and_events()
    {
        $re = $this->db
            ->select(['id', 'event', 'UNIX_TIMESTAMP(updated) AS updated', 'start_time'])
            ->from('operations')
            ->get()
            ->result_array();

        if (count($re) === 0) {
            return $re;
        }

        return array_column($re, null, 'id');
    }

    public function insert($data)
    {
        return $this->db->insert('operations', [
            'id' => $data['id'],
            'world_name' => $data['world_name'],
            'mission_name' => $data['mission_name'],
            'mission_duration' => $data['mission_duration'],
            'filename' => $data['filename'],
            'date' => $data['date'],
            'tag' => element('tag', $data, ''),
            'event' => element('event', $data, ''),
            'addon_version' => element('addon_version', $data, ''),
            'capture_delay' => element('capture_delay', $data, 0),
            'extension_build' => element('extension_build', $data, ''),
            'extension_version' => element('extension_version', $data, ''),
            'mission_author' => element('mission_author', $data, ''),
            'start_time' => element('start_time', $data, ''),
            'end_winner' => element('end_winner', $data, ''),
            'end_message' => element('end_message', $data, '')
        ]);
    }

    public function parse_entities($data)
    {
        $entities = [];
        $events = [];

        foreach ($data as $e) {
            $entities[$e['id']] = [
                'id' => $e['id'],
                'player_id' => '',
                'group_name' => element('group', $e, ''),
                'is_player' => element('isPlayer', $e, 0),
                'name' => $e['name'],
                'role' => element('role', $e, ''),
                'side' => element('side', $e, ''),
                'start_frame_num' => $e['startFrameNum'],
                'type' => $e['type'],
                'class' => element('class', $e, ''),
                'shots' => isset($e['framesFired']) ? count($e['framesFired']) : 0,
                'hits' => 0,
                'fhits' => 0,
                'kills' => 0,
                'fkills' => 0,
                'vkills' => 0,
                'deaths' => 0
            ];

            /* :UPDATE:UNIT: aka position: https://github.com/OCAP2/addon/blob/main/addons/%40ocap/addons/ocap/functions/fn_startCaptureLoop.sqf#L146
            [
                [281.652,696.751,-2.23139], // pos.x, pos.y, pos.z
                109, // direction
                1, // 0: dead, 1: awake, 2: unconscious
                0, // is_vehicle
                "PFC Arkor", // name
                1, // is_player
                "Leader" // role
            ],
            */

            if ($e['type'] === 'unit' && in_array($e['side'], ['WEST', 'EAST', 'GUER', 'CIV'])) {
                $last_state = 1; // starting state is awake, skip this one
                foreach ($e['positions'] as $f => $p) {
                    if (isset($p[5])) {
                        // not a player, not a vehicle, is a player, name field is empty and position name field is not empty
                        if (
                            $entities[$e['id']]['is_player'] === 0
                            && $p[3] === 0
                            && $p[5] === 1
                            && $e['name'] === ''
                            && $p[4] !== ''
                        ) {
                            // Note: this attempts to fix missing name and player flag for actual player units
                            $entities[$e['id']]['is_player'] = 1;
                            $entities[$e['id']]['name'] = $p[4];
                        }
                    }

                    if ($last_state !== $p[2]) {
                        $last_state = $p[2];

                        $event_name = '';
                        if ($last_state === 0) {
                            $event_name = '_dead';
                            $entities[$e['id']]['deaths'] = 1;
                        } elseif ($last_state === 1) {
                            $event_name = '_awake';
                        } elseif ($last_state === 2) {
                            $event_name = '_uncon';
                        }

                        $events[] = [
                            'frame' => $e['startFrameNum'] + $f,
                            'event' => $event_name,
                            'victim_id' => $e['id'],
                            'attacker_id' => null,
                            'weapon' => '',
                            'distance' => 0
                        ];
                    }
                }

                /*
                {"framesFired":[],"group":"B1","id":108,"isPlayer":1,"name":"","positions":[[[13687.6,17835.8],76,1,0,"",1]],"side":"GUER","startFrameNum":428,"type":"unit"}
                */
                if ($entities[$e['id']]['name'] === '') {
                    $entities[$e['id']]['is_player'] = 0;
                }
            }
        }

        return [
            'entities' => $entities,
            'events' => $events
        ];
    }

    public function parse_events($data)
    {
        $re = [
            'events' => [],
            'end_winner' => '',
            'end_message' => ''
        ];

        /** event: hit / killed
         * [0] frame nr.
         * [1] event
         * [2] victim id
         * [3][0] attacker id / "null" / -1
         * [3][1] weapon (if attacker is not null)
         * [4] distance (m)
         */
        foreach ($data as $e) {
            if ($e[1] === 'hit' || $e[1] === 'killed') {
                $attacker = null;
                $weapon = '';
                if ($e[3][0] !== 'null' && intval($e[3][0]) !== -1) {
                    $attacker = $e[3][0];
                    $weapon = element(1, $e[3], '');
                }

                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => $e[2],
                    'attacker_id' => $attacker,
                    'weapon' => $weapon,
                    'distance' => intval($e[4])
                ];
            } elseif ($e[1] === 'endMission') {
                if (is_array($e[2])) {
                    $re['end_winner'] = element(0, $e[2], '');
                    $re['end_message'] = element(1, $e[2], '');
                } else {
                    $re['end_winner'] = element(2, $e, '');
                    $re['end_message'] = element(3, $e, '');
                }
            }
        }

        return $re;
    }

    public function parse_markers($data)
    {
        $re = [];

        /** marker: projectile
         * [0] icon magIcons/*
         * [1] text
         * [2] start_frame
         * [3] end_frame
         * [4] entity_id
         * [5] color
         * [6] side
         * [7][] positions
         * [8][] marker_size
         * [9] marker_shape (RECTANGLE, ICON, POLYLINE)
         * [10] marker_brush (SolidBorder, SolidFull, solid, Solid, border)
         */
        foreach ($data as $m) {
            if ($m[0] && substr($m[0], 0, 9) === 'magIcons/') {
                if (!isset($re[$m[4]])) {
                    $re[$m[4]] = 1;
                } else {
                    $re[$m[4]]++;
                }
            }
        }

        return $re;
    }

    public function process_op_data($details, $entities, $events, $markers)
    {
        $errors = [];

        if (function_exists('preprocess_op_data')) {
            $errors = preprocess_op_data($details);
        }

        if (!$details['start_time']) {
            $details['start_time'] = gmdate('Y-m-d H:i:s', strtotime($details['date']));
        }

        if ($this->insert($details)) {

            $_dead_events = [];
            foreach ($events as $i => $e) {
                $events[$i]['operation_id'] = $details['id'];

                $aid = $e['attacker_id'];
                $vid = $e['victim_id'];

                if ($vid && $e['event'] === 'killed') {
                    if ($entities[$vid]['deaths'] === 0 || isset($_dead_events[$vid])) { // only count _dead one time to make sure it's registered
                        $entities[$vid]['deaths']++;
                    }
                    $_dead_events[$vid] = true;
                }

                if (!is_null($aid) && !is_null($vid) && $aid !== $vid && isset($entities[$aid])) {
                    $ff = ($entities[$aid]['side'] === $entities[$vid]['side']) ? true : false;

                    if ($e['event'] === 'hit') {
                        $entities[$aid]['hits']++;
                        if ($ff) {
                            $entities[$aid]['fhits']++;
                        }
                    } elseif ($e['event'] === 'killed') {
                        if ($entities[$vid]['type'] === 'unit') {
                            $entities[$aid]['kills']++;
                            if ($ff) {
                                $entities[$aid]['fkills']++;
                            }
                        } else {
                            $entities[$aid]['vkills']++;
                        }
                    }
                }
            }

            foreach ($markers as $eid => $shots) {
                if (isset($entities[$eid])) {
                    $entities[$eid]['shots'] += $shots;
                }
            }

            if ($this->db->insert_batch('events', $events) === false) {
                $errors[] = 'Failed to save events.';
            } else {
                $events = null;

                $players = $this->get_all_players();
                $player_names = array_column($players, 'name');

                $new_players = [];
                foreach ($entities as $i => $e) {
                    $entities[$i]['operation_id'] = $details['id'];

                    if ($e['is_player']) {
                        $pi = array_search($e['name'], $player_names);
                        if ($pi === false) {
                            $new_player_names = array_column($new_players, 'name');
                            $npi = array_search($e['name'], $new_player_names);

                            if ($npi === false) {
                                $new_players[] = [
                                    'entity_ids' => [$i],
                                    'name' => $e['name']
                                ];
                            } else {
                                $new_players[$npi]['entity_ids'][] = $i;
                            }
                        } else {
                            $p = $players[$pi];
                            $entities[$i]['player_id'] = $p['alias_of'] ? $p['alias_of'] : $p['id'];
                        }
                    }
                }

                if (count($new_players) > 0) {
                    $added_players = $this->add_players($new_players);
                    if ($added_players === false) {
                        $errors[] = 'Failed to save players.';
                    } else {
                        foreach ($added_players as $p) {
                            foreach ($p['entity_ids'] as $eid) {
                                $entities[$eid]['player_id'] = $p['player_id'];
                            }
                        }
                    }
                }

                if (count($errors) === 0) {
                    if ($this->db->insert_batch('entities', $entities) === false) {
                        $errors[] = 'Failed to save entities.';
                    }
                }
                $entities = null;
            }
        } else {
            $errors[] = 'Failed to save operation.';
        }

        return $errors;
    }

    private function get_all_players()
    {
        return $this->db
            ->select(['id', 'name', 'alias_of'])
            ->from('players')
            ->get()
            ->result_array();
    }

    private function add_players($new_players)
    {
        $batch = [];
        foreach ($new_players as $p) {
            $batch[] = ['name' => $p['name']];
        }

        if ($this->db->insert_batch('players', $batch) === false) {
            return false;
        } else {
            $first_id = $this->db->insert_id();

            foreach ($new_players as $i => $p) {
                $new_players[$i]['player_id'] = $first_id++;
            }
        }

        return $new_players;
    }

    public function get_ops($events_filter, $id = false, $empty_end_winner_only = false)
    {
        if (is_array($events_filter) && count($events_filter) > 0) {
            $this->db->where_in('operations.event', $events_filter);
        } elseif ($events_filter !== false) {
            $this->db->where('operations.event !=', '');
        }

        if ($id !== false) {
            if (!is_array($id)) {
                $id = [$id];
            }

            $this->db->where_in('operations.id', $id);
        }

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
                'operations.end_message'
            ])
            ->select('COUNT(DISTINCT entities.player_id) AS players')
            ->from('operations')
            ->join('entities', 'entities.operation_id = operations.id AND entities.player_id != 0', 'LEFT')
            ->group_by('operations.id')
            ->order_by('operations.id', 'DESC');

        if ($empty_end_winner_only) {
            $this->db->where('operations.end_winner', '');
        }

        return $this->db
            ->get()
            ->result_array();
    }

    public function get_by_id($id, $only_parsed = true)
    {
        $re = $this->get_ops($only_parsed, $id);

        if (count($re) === 0) {
            return false;
        } else {
            return $re[0];
        }
    }

    public function get_entities_by_op_id($id)
    {
        $this->db
            ->select([
                'entities.operation_id',
                'entities.id',
                'entities.player_id',
                'entities.group_name',
                'entities.is_player',
                'entities.name',
                'entities.role',
                'entities.side',
                'entities.start_frame_num',
                'entities.type',
                'entities.class',
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
            ->join('players', 'players.id = entities.player_id', 'LEFT')
            ->join('operations', 'operations.id = entities.operation_id')
            ->where('entities.operation_id', $id)
            ->group_by('entities.id')
            ->order_by('is_player DESC, kills DESC, deaths ASC, hits DESC, vkills DESC, shots ASC, id ASC');

        return $this->db->get()->result_array();
    }

    public function purge($id)
    {
        $del_events = $this->db->delete('events', ['operation_id' => $id]);
        $del_entities = $this->db->delete('entities', ['operation_id' => $id]);
        $del_operation = $this->db->delete('operations', ['id' => $id]);

        if ($del_events && $del_entities && $del_operation) {
            return false;
        } else {
            $errors = [];
            if ($del_events === false) {
                $errors[] = 'Error when deleting from events table. ';
            }
            if ($del_entities === false) {
                $errors[] = 'Error when deleting from entities table. ';
            }
            if ($del_operation === false) {
                $errors[] = 'Error when deleting from operations table. ';
            }

            return $errors;
        }
    }

    public function get_events_by_op_id($id)
    {
        $this->db
            ->select([
                'events.frame',
                'events.event',
                'events.weapon',
                'events.distance',

                'victim.name AS victim_name',
                'victim.side AS victim_side',
                'attacker.name AS attacker_name',
                'attacker.side AS attacker_side',

                'victim_player.name AS victim_player_name',
                'victim_player.id AS victim_player_id',
                'attacker_player.name AS attacker_player_name',
                'attacker_player.id AS attacker_player_id'
            ])
            ->from('events')
            ->join('entities AS victim', 'victim.id = events.victim_id AND victim.operation_id = ' . $id)
            ->join('entities AS attacker', 'attacker.id = events.attacker_id AND attacker.operation_id = ' . $id, 'LEFT')
            ->join('players AS victim_player', 'victim_player.id = victim.player_id', 'LEFT')
            ->join('players AS attacker_player', 'attacker_player.id = attacker.player_id', 'LEFT')
            ->where('events.operation_id', $id)
            ->order_by("events.frame ASC, victim.name ASC, FIELD(events.event, 'hit', 'killed', '_awake', '_uncon', '_dead')");

        return $this->db->get()->result_array();
    }
}
