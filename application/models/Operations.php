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

    public function get_all_ops()
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

    public function insert_time($data)
    {
        /*
        "times":[
            {
                "date":"2022-01-26T20:28:00",
                "frameNum":0,
                "systemTimeUTC":"2021-12-26T20:14:50.22",
                "time":0.0,
                "timeMultiplier":1.0
            },
            {
                "date":"2022-01-26T20:28:00",
                "frameNum":10,
                "systemTimeUTC":"2021-12-26T20:20:08.71",
                "time":10.006,
                "timeMultiplier":1.0
            }
        ]
        */
        return $this->db->insert('timestamps', [
            'operation_id' => $data['operation_id'],
            'id' => $data['id'],
            'date' => $data['date'],
            'frame_num' => $data['frameNum'],
            'sys_time_utc' => $data['systemTimeUTC'],
            'time' => $data['time'],
            'time_multiplier' => $data['timeMultiplier']
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
                'last_frame_num' => null,
                'type' => $e['type'],
                'class' => element('class', $e, ''),
                'shots' => isset($e['framesFired']) ? count($e['framesFired']) : 0,
                'hits' => 0,
                'fhits' => 0,
                'kills' => 0,
                'fkills' => 0,
                'vkills' => 0,
                'deaths' => 0,
                '_deaths' => 0,
                'distance_traveled' => 0,
                'uid' => element('uid', $e, null)
            ];

            $last_xyz = null;
            $last_crew = [];
            $last_state = 1; // starting state is awake, skip this one
            foreach ($e['positions'] as $f => $p) {
                $current_frame = $e['startFrameNum'] + $f;
                if ($entities[$e['id']]['last_frame_num'] === null || $current_frame > $entities[$e['id']]['last_frame_num']) {
                    $entities[$e['id']]['last_frame_num'] = $current_frame;
                }

                // skip frame if empty
                if (empty($p)) {
                    continue;
                }

                if ($last_xyz !== null) {
                    $distance = sqrt(pow($last_xyz[0] - $p[0][0], 2) + pow($last_xyz[1] - $p[0][1], 2) + pow(element(2, $last_xyz, 0) - element(2, $p[0], 0), 2));
                    $entities[$e['id']]['distance_traveled'] += $distance;
                }
                $last_xyz = $p[0];

                if ($e['type'] === 'unit' && in_array($e['side'], ['WEST', 'EAST', 'GUER', 'CIV'])) {
                    /* :UPDATE:UNIT: (https://github.com/OCAP2/addon/blob/main/addons/%40ocap/addons/ocap/functions/fn_startCaptureLoop.sqf#L146)
                    [
                        [281.652,696.751,-2.23139], // pos.x, pos.y, pos.z
                        109, // direction
                        1, // 0: dead, 1: awake, 2: unconscious
                        0, // in vehicle
                        "PFC Arkor", // name
                        1, // is player
                        "Leader" // role
                    ]
                    */
                    if (isset($p[5])) {
                        // entity not a player, not in a vehicle, is a player, name field is empty and position name field is not empty
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
                } elseif ($e['type'] === 'vehicle') {
                    /* :UPDATE:VEH: (https://github.com/OCAP2/addon/blob/main/addons/%40ocap/addons/ocap/functions/fn_startCaptureLoop.sqf#L198)
                    [
                        [281.652,696.751,-2.23139], // pos.x, pos.y, pos.z
                        109, // direction
                        1, // 0: dead, 1: awake, 2: unconscious
                        [], // crew IDs (driver, gunner, commander, turrets, cargo)
                        [30,30], // from frame, until frame
                    ]
                    */
                    if (isset($p[4]) && is_array($p[4])) {
                        $current_frame = $p[4][0];
                        $entities[$e['id']]['last_frame_num'] = $p[4][1];
                    }

                    $entered = array_diff($p[3], $last_crew);
                    $exited = array_diff($last_crew, $p[3]);

                    foreach ($entered as $eid) {
                        $events[] = [
                            'frame' => $current_frame,
                            'event' => '_enter_vehicle',
                            'victim_id' => $e['id'],
                            'attacker_id' => $eid,
                            'weapon' => null,
                            'distance' => 0,
                            'data' => null
                        ];
                    }

                    foreach ($exited as $eid) {
                        $events[] = [
                            'frame' => $current_frame,
                            'event' => '_exit_vehicle',
                            'victim_id' => $e['id'],
                            'attacker_id' => $eid,
                            'weapon' => null,
                            'distance' => 0,
                            'data' => null
                        ];
                    }

                    $last_crew = $p[3];
                }

                if ($last_state !== $p[2]) {
                    $last_state = $p[2];

                    $event_name = '';
                    if ($last_state === 0) {
                        $event_name = '_dead';
                        $entities[$e['id']]['_deaths']++;
                    } elseif ($last_state === 1) {
                        $event_name = '_awake';
                    } elseif ($last_state === 2) {
                        $event_name = '_uncon';
                    }

                    $events[] = [
                        'frame' => $current_frame,
                        'event' => $event_name,
                        'victim_id' => $e['id'],
                        'attacker_id' => null,
                        'weapon' => null,
                        'distance' => 0,
                        'data' => null
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

        foreach ($data as $e) {
            /** event: hit / killed
             * [0] frame nr.
             * [1] event
             * [2] victim id
             * [3][0] attacker id / "null" / -1
             * [3][1] weapon (if attacker is not null)
             * [4] distance (m)
             */
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
                    'distance' => intval($e[4]),
                    'data' => null
                ];
            }
            /** event: endMission
             * [0] frame nr.
             * [1] event
             * [2][0] winner
             * [2][1] message
             */
            elseif ($e[1] === 'endMission') {
                if (is_array($e[2])) {
                    $re['end_winner'] = element(0, $e[2], '');
                    $re['end_message'] = element(1, $e[2], '');
                } else {
                    $re['end_winner'] = element(2, $e, '');
                    $re['end_message'] = element(3, $e, '');
                }
            }
            /** event: connected / disconnected
             * [0] frame nr.
             * [1] event
             * [2] message
             */
            elseif ($e[1] === 'connected' || $e[1] === 'disconnected') {
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => $e[2]
                ];
            }
            /** event: capturedFlag
             * [0] frame nr.
             * [1] event
             * [2][0] unit name
             * [2][1] unit color
             * [2][2] objective color
             * [2][3] objective position
             */
            elseif ($e[1] === 'capturedFlag') {
                array_unshift($e[2], 'flag');
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => 'captured',
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => json_encode($e[2])
                ];
            }
            /** event: captured
             * [0] frame nr.
             * [1] event
             * [2][0] capture type (eg.: flag)
             * [2][1] unit name
             * [2][2] unit color
             * [2][3] objective color
             * [2][4] objective position
             */
            elseif ($e[1] === 'captured') {
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => json_encode($e[2])
                ];
            }
            /** event: terminalHackStarted / terminalHackUpdate / terminalHackCanceled
             * [0] frame nr.
             * [1] event
             * [2][0] unit name
             * [2][1] unit color
             * [2][2] terminal color
             * [2][3] terminal identifier
             * [2][4] terminal position
             * [2][5] countdown timer
             */
            elseif ($e[1] === 'terminalHackStarted' || $e[1] === 'terminalHackUpdate' || $e[1] === 'terminalHackCanceled') {
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => json_encode($e[2])
                ];
            }
            /** event: generalEvent
             * [0] frame nr.
             * [1] event
             * [2] message
             */
            elseif ($e[1] === 'generalEvent') {
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => $e[2]
                ];
            }
            /** event: respawnTickets
             * [0] frame nr.
             * [1] event
             * [2] ?tickets
             */
            elseif ($e[1] === 'respawnTickets') {
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => json_encode($e[2])
                ];
            }
            /** event: counterInit
             * [0] frame nr.
             * [1] event
             * [2] ?counter, sides
             */
            elseif ($e[1] === 'counterInit') {
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => json_encode($e[2])
                ];
            }
            /** event: counterSet
             * [0] frame nr.
             * [1] event
             * [2] ?scores
             */
            elseif ($e[1] === 'counterSet') {
                $re['events'][] = [
                    'frame' => $e[0],
                    'event' => $e[1],
                    'victim_id' => null,
                    'attacker_id' => null,
                    'weapon' => null,
                    'distance' => 0,
                    'data' => json_encode($e[2])
                ];
            }
            /** show what needs to be implemented
             */
            else {
                log_message('error', 'Unknown event type: ' . strval($e[1]) . ' @ frame ' . strval($e[0]));
            }
        }

        return $re;
    }

    public function parse_markers($data)
    {
        $shots = [];
        $events = [];

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
            // ["magIcons/rhs_vog25p_ca.paa","PBG40 - HE-T Grenade",981,992,54,"FFFFFF",-1,[[981,[4590.9,7109.79,201.889],238.414,1],[982,[4589.02,7108.63,204.324],238.414,1]],[1,1],"ICON","Solid"]
            if ($m[0] && (substr($m[0], 0, 9) === 'magIcons/' || $m[0] === 'Minefield' || $m[0] === 'mil_triangle')) {
                if (!isset($shots[$m[4]])) {
                    $shots[$m[4]] = 1;
                } else {
                    $shots[$m[4]]++;
                }

                $distance = 0;
                if (isset($m[7]) && is_array($m[7])) {
                    $first_pos = reset($m[7]);
                    $last_pos = end($m[7]);
                    $distance = sqrt(pow($first_pos[1][0] - $last_pos[1][0], 2) + pow($first_pos[1][1] - $last_pos[1][1], 2) + pow(element(2, $first_pos[1], 0) - element(2, $last_pos[1], 0), 2));
                }

                $events[] = [
                    'frame' => $m[2],
                    'event' => '_projectile',
                    'victim_id' => null,
                    'attacker_id' => $m[4],
                    'weapon' => $m[1],
                    'distance' => $distance,
                    'data' => json_encode(array_slice($m, 0, 7))
                ];
            }
        }

        return [
            'shots' => $shots,
            'events' => $events
        ];
    }

    public function process_op_data($details, $entities, $events, $markers, $times)
    {
        $errors = [];

        if (function_exists('preprocess_op_data')) {
            $errors = preprocess_op_data($details);
        }

        if (!$details['start_time']) {
            $details['start_time'] = gmdate('Y-m-d H:i:s', strtotime($details['date']));
        }

        foreach ($times as $ti => $t) {
            $t['operation_id'] = $details['id'];
            $t['id'] = $ti;
            if (!$this->insert_time($t)) {
                $errors[] = 'Failed to save timestamp. #' . $ti;
            }
        }

        foreach ($markers['shots'] as $eid => $n) {
            if (isset($entities[$eid])) {
                $entities[$eid]['shots'] += $n;
            }
        }

        if ($this->insert($details)) {
            foreach ($events as $i => $e) {
                $events[$i]['operation_id'] = $details['id'];

                $aid = $e['attacker_id'];
                $vid = $e['victim_id'];

                if (!is_null($vid) && $e['event'] === 'killed') {
                    $entities[$vid]['deaths']++;
                }

                if (in_array($e['event'], ['hit', 'killed']) && !is_null($aid) && !is_null($vid) && $aid !== $vid) {
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

            if ($this->db->insert_batch('events', $events) === false) {
                $errors[] = 'Failed to save events.';
            } else {
                $events = null;

                $players = $this->get_all_players();

                $new_players = [];
                $players_updates = [];
                $alias_reassignments = [];
                foreach ($entities as $i => $e) {
                    $entities[$i]['operation_id'] = $details['id'];

                    $entities[$i]['deaths'] = max($e['deaths'], $e['_deaths']);
                    unset($entities[$i]['_deaths']);

                    if ($e['is_player']) {
                        if ($e['uid'] !== null && $e['uid'] !== '' && $e['uid'] !== 0) {
                            $player_uids = array_column($players, 'uid');
                            $pi = array_search($e['uid'], $player_uids);
                            if ($pi === false) {
                                $new_player_uids = array_column($new_players, 'uid');
                                $npi = array_search($e['uid'], $new_player_uids);

                                if ($npi === false) {
                                    $new_players[] = [
                                        'entity_ids' => [$i],
                                        'name' => $e['name'],
                                        'uid' => $e['uid']
                                    ];
                                } else {
                                    $new_players[$npi]['entity_ids'][] = $i;
                                    if ($e['name'] !== $new_players[$npi]['name']) {
                                        $new_players[$npi]['name'] = $e['name'];
                                    }
                                }
                            } else {
                                // Note: a player with a uid can never be an alias
                                $p = $players[$pi];
                                $entities[$i]['player_id'] = $p['id'];
                                if ($e['name'] !== $p['name']) {
                                    $players_updates[$p['id']] = [
                                        'id' => $p['id'],
                                        'name' => $e['name']
                                    ];
                                }
                            }
                        } else {
                            $player_names = array_column(array_filter($players, function ($v) {
                                return $v['uid'] === null;
                            }), 'name');
                            $pi = array_search(strtolower($e['name']), array_map('strtolower', $player_names));
                            // Note: dupes can still occur after the entity name is inserted into the db and whitespaces get trimmed
                            // SELECT * FROM players WHERE name IN (SELECT name FROM players GROUP BY name HAVING COUNT(*) > 1) ORDER BY name, id
                            if ($pi === false) {
                                $new_player_names = array_column($new_players, 'name');
                                $npi = array_search(strtolower($e['name']), array_map('strtolower', $new_player_names));

                                if ($npi === false) {
                                    $new_players[] = [
                                        'entity_ids' => [$i],
                                        'name' => $e['name'],
                                        'uid' => null
                                    ];
                                } else {
                                    $new_players[$npi]['entity_ids'][] = $i;
                                }
                            } else {
                                $p = $players[$pi];

                                if ($p['alias_of']) {
                                    $entities[$i]['player_id'] = $p['alias_of'];
                                    $alias_reassignments[$p['alias_of']] = $p['id'];
                                } else {
                                    $entities[$i]['player_id'] = $p['id'];
                                }
                            }
                        }
                    }
                }

                if (count($new_players) > 0) {
                    $added_players = $this->add_players($new_players);
                    $errors = array_merge($errors, $added_players['errors']);
                    foreach ($added_players['new_players'] as $p) {
                        foreach ($p['entity_ids'] as $eid) {
                            $entities[$eid]['player_id'] = $p['player_id'];
                        }
                    }
                }

                if (count($errors) === 0) {
                    if ($this->db->insert_batch('entities', $entities) === false) {
                        $errors[] = 'Failed to save entities.';
                    }
                }
                $entities = null;

                if (count($players_updates) > 0) {
                    $err = $this->update_players($players_updates);
                    $errors = array_merge($errors, $err);
                }

                if (count($alias_reassignments) > 0) {
                    $err = $this->reassign_aliases($alias_reassignments);
                    $errors = array_merge($errors, $err);
                }
            }
        } else {
            $errors[] = 'Failed to save operation.';
        }

        return $errors;
    }

    public function purge($id)
    {
        $del_timestamps = $this->db->delete('timestamps', ['operation_id' => $id]);
        $del_events = $this->db->delete('events', ['operation_id' => $id]);
        $del_entities = $this->db->delete('entities', ['operation_id' => $id]);
        $del_operation = $this->db->delete('operations', ['id' => $id]);

        if ($del_events && $del_entities && $del_operation) {
            return false;
        } else {
            $errors = [];

            if ($del_timestamps === false) {
                $errors[] = 'Error when deleting from timestamps table. ';
            }
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

    private function get_all_players()
    {
        return $this->db
            ->select(['id', 'name', 'alias_of', 'uid'])
            ->from('players')
            ->get()
            ->result_array();
    }

    private function add_players($new_players)
    {
        $errors = [];

        foreach ($new_players as $i => $p) {
            if ($this->db->insert('players', [
                'name' => $p['name'],
                'uid' => $p['uid']
            ]) === false) {
                $errors[] = 'Failed to create new player (' . $p['name'] . '/' . $p['uid'] . ')';
            } else {
                $new_players[$i]['player_id'] = $this->db->insert_id();
            }
        }

        return [
            'errors' => $errors,
            'new_players' => $new_players
        ];
    }

    private function update_players($players)
    {
        $errors = [];

        if (count($players) > 0) {
            if (false === $this->db->update_batch('players', $players, 'id')) {
                $errors[] = 'Failed to update player names.';
            }
        }

        return $errors;
    }

    private function reassign_aliases($alias_reassignments)
    {
        $errors = [];

        if (count($alias_reassignments) > 0) {
            foreach ($alias_reassignments as $new_alias_id => $player_id) {

                $this->db->where('id', $player_id);
                if ($this->db->update('players', ['alias_of' => 0])) {

                    $this->db->where('player_id', $new_alias_id);
                    if ($this->db->update('entities', ['player_id' => $player_id])) {

                        $this->db->where('id', $new_alias_id);
                        $this->db->or_where('alias_of', $new_alias_id);
                        if (!$this->db->update('players', ['alias_of' => $player_id])) {
                            $errors[] = 'Failed to update player aliases. (' . $new_alias_id . ')';
                        }
                    } else {
                        $errors[] = 'Failed to update player ID of entities. (' . $player_id . ')';
                    }
                } else {
                    $errors[] = 'Failed to update player alias. (' . $player_id . ')';
                }
            }
        }

        return $errors;
    }

    public function get_ops($events_filter, $id = false)
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
                'operations.capture_delay',
                'operations.mission_author',
                'operations.start_time',
                'operations.end_winner',
                'operations.end_message',
                'operations.verified'
            ])
            ->select('COUNT(DISTINCT entities.player_id) AS players_total')
            ->from('operations')
            ->join('entities', 'entities.operation_id = operations.id AND entities.player_id != 0', 'LEFT')
            ->group_by('operations.id')
            ->order_by('operations.id DESC');

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

    public function get_entities_by_id($id)
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
                'entities.start_frame_num',
                'entities.type',
                'entities.class',
                'entities.distance_traveled',
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
            ->join('players', 'players.id = entities.player_id', 'LEFT')
            ->where('entities.operation_id', $id)
            ->group_by('entities.id')
            ->order_by('is_player DESC, kills DESC, deaths ASC, hits DESC, vkills DESC, shots ASC, entities.id ASC');

        return $this->db->get()->result_array();
    }

    public function get_events_by_id($id)
    {
        $this->db
            ->select([
                'events.frame',
                'events.event',
                'events.weapon',
                'events.distance',
                'events.data',
                'events.victim_id',
                'events.attacker_id',
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
            ->join('entities AS victim', 'victim.id = events.victim_id AND victim.operation_id = ' . $id, 'LEFT')
            ->join('entities AS attacker', 'attacker.id = events.attacker_id AND attacker.operation_id = ' . $id, 'LEFT')
            ->join('players AS victim_player', 'victim_player.id = victim.player_id', 'LEFT')
            ->join('players AS attacker_player', 'attacker_player.id = attacker.player_id', 'LEFT')
            ->where('events.operation_id', $id)
            ->order_by("events.frame ASC, victim.name ASC, FIELD(events.event, 'hit', 'killed', '_awake', '_uncon', '_dead')");

        return $this->db->get()->result_array();
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
            ->select('COUNT(DISTINCT events.attacker_id) AS players_total')
            ->from('events')
            ->join('entities AS attacker', 'attacker.id = events.attacker_id AND attacker.operation_id = events.operation_id')
            ->join('entities AS victim', 'victim.id = events.victim_id AND victim.operation_id = events.operation_id')
            ->where('events.operation_id', $id)
            ->where('victim.player_id != attacker.player_id')
            ->where_in('events.event', ['hit', 'killed'])
            ->where('events.weapon !=', '')
            ->group_by('events.weapon')
            ->order_by('kills DESC, hits DESC, fkills ASC, fhits ASC, events.weapon ASC');

        return $this->db->get()->result_array();
    }
}
