<?php
defined('BASEPATH') or exit('No direct script access allowed');

$sides = $this->config->item('sides');

if (count($items) === 0) {
    echo '<div class="mdc-typography--body1 list__no_items">No events found...</div>';
} else {
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' events</div>';
}

$deduped_items = array_reduce($items, function ($acc, $next) {
    if ($acc === null) {
        $next['_count'] = 1;

        return [$next];
    } else {
        $last_key = key(array_slice($acc, -1, 1, true));
        $last_val = $acc[$last_key];
        unset($last_val['_count']);

        if ($next == $last_val) {
            $acc[$last_key]['_count']++;
        } else {
            $next['_count'] = 1;
            $acc[] = $next;
        }
    }

    return $acc;
});
?>
<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
            <div class="mdc-data-table mdc-elevation--z2" id="events-table">
                <div class="mdc-data-table__table-container">

                    <?php echo $op_menu; ?>

                    <table class="mdc-data-table__table sortable">
                        <thead>
                            <?php if (count($items) > 0) : ?>
                                <tr class="mdc-data-table__header-row dnone" id="events-filters">
                                    <td class="mdc-data-table__header-cell"></td>
                                    <td class="mdc-data-table__header-cell" id="attacker-filter"></td>
                                    <td class="mdc-data-table__header-cell" id="event-filter"></td>
                                    <td class="mdc-data-table__header-cell" id="victim-filter"></td>
                                    <td class="mdc-data-table__header-cell"></td>
                                    <td class="mdc-data-table__header-cell"></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="mdc-data-table__header-row">
                                <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="ascending" data-column-id="time">Time</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="attacker" title="Player name / Entity ID">Attacker</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="event">Event</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="victim" title="Player name / Entity ID">Victim</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="weapon">Weapon</th>
                                <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="distance">Distance</th>
                            </tr>
                        </thead>
                        <tbody class="mdc-data-table__content">
                            <?php 
                            $events_num = [];
                            foreach ($deduped_items as $index => $i) :
                                if (isset($events_num[$i['event']])) {
                                    $events_num[$i['event']] += 1;
                                } else {
                                    $events_num[$i['event']] = 1;
                                }

                                $time = gmdate('H:i:s', $i['frame']);

                                $attacker_player_name = is_null($i['attacker_player_name']) ? '' : $i['attacker_player_name'];
                                $attacker_name = html_escape($i['attacker_name']);
                                $attacker_side_class = $i['attacker_side'] ? 'side__' . html_escape(strtolower($i['attacker_side'])) : '';
                                $attacker_title = '';
                                if ($i['attacker_player_id']) {
                                    $attacker_name = '<a href="' . base_url('player/') . $i['attacker_player_id'] . '">' . $attacker_name . '</a>';
                                    if ($attacker_player_name !== '' && $i['attacker_name'] !== $attacker_player_name) {
                                        $attacker_title = ' title="' . html_escape($attacker_player_name) . '"';
                                    }
                                } elseif ($i['attacker_id'] !== null) {
                                    $attacker_name = '<span title="#' . $i['attacker_id'] . '">' . $attacker_name . '</span>';
                                }
                                $attacker_medals = [];
                                if (isset($players_first_op[$i['attacker_player_id']]) && $players_first_op[$i['attacker_player_id']] === $op_id) {
                                    $attacker_medals[] = '<span>👶</span>';
                                }
                                if (isset($op_commanders[$i['attacker_side']]) && $op_commanders[$i['attacker_side']]['entity_id'] === $i['attacker_id']) {
                                    $attacker_medals[] = '<span class="side__' . html_escape(strtolower($i['attacker_side'])) . '">🎖</span>';
                                }

                                $victim_player_name = is_null($i['victim_player_name']) ? '' : $i['victim_player_name'];
                                $victim_name = html_escape($i['victim_name']);
                                $victim_side_class = $i['victim_side'] ? 'side__' . html_escape(strtolower($i['victim_side'])) : '';
                                $victim_title = '';
                                if ($i['victim_player_id']) {
                                    $victim_name = '<a href="' . base_url('player/') . $i['victim_player_id'] . '">' . $victim_name . '</a>';
                                    if ($victim_player_name !== '' && $i['victim_name'] !== $victim_player_name) {
                                        $victim_title = ' title="' . html_escape($victim_player_name) . '"';
                                    }
                                } elseif ($i['victim_id'] !== null) {
                                    $victim_name = '<span title="#' . $i['victim_id'] . '">' . $victim_name . '</span>';
                                }
                                $victim_medals = [];
                                if (isset($players_first_op[$i['victim_player_id']]) && $players_first_op[$i['victim_player_id']] === $op_id) {
                                    $victim_medals[] = '<span>👶</span>';
                                }
                                if (isset($op_commanders[$i['victim_side']]) && $op_commanders[$i['victim_side']]['entity_id'] === $i['victim_id']) {
                                    $victim_medals[] = '<span class="side__' . html_escape(strtolower($i['victim_side'])) . '">🎖</span>';
                                }

                                $distance = '';
                                if ($i['distance'] > 0) {
                                    $distance = html_escape($i['distance']) . ' m';
                                }

                                $event = html_escape($i['event']);
                                if ($i['event'] === 'connected' || $i['event'] === 'disconnected') {
                                    $event = html_escape($i['data']) . ' ' . $event;
                                } elseif ($i['event'] === 'captured') {
                                    $d = json_decode($i['data']);
                                    $event = html_escape($d[1]) . ' captured ' . html_escape($d[0]);
                                } elseif ($i['event'] === 'terminalHackStarted' || $i['event'] === 'terminalHackUpdate' || $i['event'] === 'terminalHackCanceled') {
                                    $d = json_decode($i['data']);
                                    $event = $event . ' by ' . html_escape($d[0]);
                                } elseif ($i['event'] === 'generalEvent') {
                                    $event = html_escape($i['data']);
                                } elseif (in_array($i['event'], ['respawnTickets', 'counterInit', 'counterSet'])) {
                                    $event = $event . ': <pre>' . html_escape($i['data']) . '</pre>';
                                }
                            ?>
                                <tr class="mdc-data-table__row" data-event-name="<?php echo html_escape($i['event']); ?>" data-attacker-id="<?php echo $i['attacker_id']; ?>" data-victim-id="<?php echo $i['victim_id']; ?>">
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $time; ?></td>
                                    <td class="mdc-data-table__cell cell__title <?php echo $attacker_side_class; ?>">
                                        <span<?php echo $attacker_title; ?>><?php echo $attacker_name; ?></span><?php echo implode(' ', $attacker_medals); ?>
                                    </td>
                                    <td class="mdc-data-table__cell" data-sort="<?php echo html_escape($i['event']); ?>">
                                        <?php echo $event; ?>
                                        <?php
                                        if ($i['_count'] > 1) {
                                            echo ' <small>&#xd7;' . $i['_count'] . '</small>';
                                        }
                                        ?>
                                    </td>
                                    <td class="mdc-data-table__cell cell__title <?php echo $victim_side_class; ?>">
                                        <span<?php echo $victim_title; ?>><?php echo $victim_name; ?></span><?php echo implode(' ', $victim_medals); ?>
                                    </td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($i['weapon']); ?></td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo html_escape($i['distance']); ?>"><?php echo $distance; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/slim-select@2.8.1/dist/slimselect.min.js"></script>
<script>
    const ss_css = document.createElement('link');
    ss_css.rel = 'stylesheet';
    ss_css.href = '<?php echo base_url('public/slimselect2.css'); ?>';
    document.head.appendChild(ss_css);

    const entities = <?php echo json_encode($op_entities); ?>;
    const sides = <?php echo json_encode($sides); ?>;
    const events_num = <?php echo json_encode($events_num); ?>;

    function deepMerge(obj1, obj2) {
        const merged = {};
        for (const key in obj1) {
            if (obj1.hasOwnProperty(key)) {

                if (Array.isArray(obj1[key])) {
                    merged[key] = [...obj1[key], ...obj2[key] || []];
                } else if (typeof obj1[key] === 'object') {
                    merged[key] = deepMerge(obj1[key], obj2[key] || {});
                } else {
                    merged[key] = obj1[key];
                }
            }
        }
        for (const key in obj2) {
            if (obj2.hasOwnProperty(key) && !merged.hasOwnProperty(key)) {
                merged[key] = obj2[key];
            }
        }
        return merged;
    }

    const events_filters = document.getElementById('events-filters');

    if (events_filters) {
        const attacker_filter = document.getElementById('attacker-filter');
        const victim_filter = document.getElementById('victim-filter');
        const event_filter = document.getElementById('event-filter');

        const side_player_entities = {};
        const side_other_entities = {};
        for (const ent of entities) {
            if (parseInt(ent.is_player, 10) === 1) {
                if (!side_player_entities[ent.side]) {
                    side_player_entities[ent.side] = [{
                        text: '=== 👤 ===',
                        disabled: true
                    }];
                }
                side_player_entities[ent.side].push({
                    text: '#' + ent.id + ' ' + ent.name,
                    value: ent.id
                });
            } else {
                if (!side_other_entities[ent.side]) {
                    side_other_entities[ent.side] = [{
                        text: '=== 🤖🚓🚁🔫 ===',
                        disabled: true
                    }];
                }
                side_other_entities[ent.side].push({
                    text: '#' + ent.id + ' ' + ent.name,
                    value: ent.id
                });
            }
        }

        const side_entities = deepMerge(side_player_entities, side_other_entities);
        const ss_entities_data_field = Object.keys(side_entities).map(side => {
            return {
                label: sides[side] || '❓',
                options: side_entities[side],
                closable: 'open'
            }
        });
        
        const attacker_select = document.createElement('select');
        attacker_select.classList.add('attacker-filter-ss');
        attacker_filter.appendChild(attacker_select);
        const attacker_ss = new SlimSelect({
            'select': attacker_select,
            'settings': {
                showSearch: true,
                allowDeselect: true
            },
            'data': [
                {
                    text: '',
                    placeholder: true
                },
                {
                    text: 'nobody / "something"',
                    value: 'null'
                },
                ...ss_entities_data_field
            ],
            events: {
                afterChange: () => {
                    update_events_ss_dataset();
                    update_events_table_classes();
                }
            }
        });

        const victim_select = document.createElement('select');
        victim_select.classList.add('attacker-filter-ss');
        victim_filter.appendChild(victim_select);
        const victim_ss = new SlimSelect({
            'select': victim_select,
            'settings': {
                showSearch: true,
                allowDeselect: true
            },
            'data': [
                {
                    text: '',
                    placeholder: true
                },
                {
                    text: 'nobody / "something"',
                    value: 'null'
                },
                ...ss_entities_data_field
            ],
            events: {
                afterChange: () => {
                    update_events_ss_dataset();
                    update_events_table_classes();
                }
            }
        });

        const ss_events_data_field = Object.keys(events_num).map(ev => {
            return {
                text: ev + ' (' + events_num[ev] + ')',
                value: ev
            }
        });

        const event_select = document.createElement('select');
        event_select.multiple = true;
        event_select.classList.add('attacker-filter-ss');
        event_filter.appendChild(event_select);
        const event_ss = new SlimSelect({
            'select': event_select,
            'settings': {
                showSearch: true,
                allowDeselect: true,
                closeOnSelect: false
            },
            'data': [
                {
                    text: '',
                    placeholder: true
                },
                ...ss_events_data_field
            ],
            events: {
                afterChange: () => {
                    update_events_table_classes();
                }
            }
        });

        function update_events_ss_dataset () {
            if (!event_ss) return;

            const rules = [];

            const attacker_ss_value = attacker_ss.getSelected();
            if (attacker_ss_value.length && attacker_ss_value[0] !== '') {
                const id = attacker_ss_value[0] === 'null' ? '' : attacker_ss_value[0];
                rules.push('[data-attacker-id="' + attacker_ss_value + '"]');
            }

            const victim_ss_value = victim_ss.getSelected();
            if (victim_ss_value.length && victim_ss_value[0] !== '') {
                const id = victim_ss_value[0] === 'null' ? '' : victim_ss_value[0];
                rules.push('[data-victim-id="' + victim_ss_value + '"]');
            }

            const event_ss_value = event_ss.getSelected();

            const ss_events_data_field_new = Object.keys(events_num).sort().map(ev => {

                const count = document.querySelectorAll('#events-table tbody tr[data-event-name="' + ev + '"]' + rules.join('')).length;
                return {
                    text: ev + ' (' + count + ')',
                    value: ev,
                    selected: event_ss_value.includes(ev)
                }
            });

            event_ss.setData(ss_events_data_field_new);
        }

        function update_events_table_classes() {
            let attacker_id = false;
            const attacker_ss_value = attacker_ss.getSelected();
            if (attacker_ss_value.length && attacker_ss_value[0] !== '') {
                attacker_id = attacker_ss_value[0] === 'null' ? '' : attacker_ss_value[0];
            }

            let victim_id = false;
            const victim_ss_value = victim_ss.getSelected();
            if (victim_ss_value.length && victim_ss_value[0] !== '') {
                victim_id = victim_ss_value[0] === 'null' ? '' : victim_ss_value[0];
            }

            const event_ss_value = event_ss.getSelected();

            const rows = document.querySelectorAll('#events-table tbody tr');
            for (const tr of rows) {
                if (event_ss_value.length > 0) {
                    if (!event_ss_value.includes(tr.dataset.eventName)) {
                        tr.classList.add('dnone');
                        continue;
                    }
                }

                if (attacker_id !== false && attacker_id !== tr.dataset.attackerId) {
                    tr.classList.add('dnone');
                    continue;
                }

                if (victim_id !== false && victim_id !== tr.dataset.victimId) {
                    tr.classList.add('dnone');
                    continue;
                }

                tr.classList.remove('dnone');
            }
        }

        events_filters.classList.remove('dnone');
    }
</script>