<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
$sides = $this->config->item('sides');

$warn_icon = '<span class="material-icons">warning</span>';
$flaky_icon = '<span class="material-icons">flaky</span>';
$fixed_icon = '<span class="material-icons">check</span>';
?>
<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">

        <?php if (count($errors) > 0) : ?>
            <div class="errors mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <h3>⚠️ Errors</h3>
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <?php if ($op) :
            $duration_min = floor(intval($op['mission_duration']) / 60);
            $duration_sec = floor(intval($op['mission_duration']) % 60);

            $verified = boolval(intval($op['verified']));
            $verified_attr = $verified ? ' disabled' : '';
            $verified_class = $verified ? ' mdc-text-field--disabled' : '';
        ?>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <div class="mdc-tab-bar">
                            <div class="mdc-tab-scroller">
                                <div class="mdc-tab-scroller__scroll-area">
                                    <div class="mdc-tab-scroller__scroll-content">
                                        <a href="<?php echo base_url('manage/' . $op['id']); ?>" class="mdc-tab" role="tab" aria-selected="false" tabindex="5">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Process data</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/verify'); ?>" class="mdc-tab" role="tab" aria-selected="false" tabindex="6">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Op</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/entities'); ?>" class="mdc-tab" role="tab" aria-selected="false" tabindex="7">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Entities</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/events'); ?>" class="mdc-tab mdc-tab--active" role="tab" aria-selected="true" tabindex="8">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Events</span>
                                            </span>
                                            <span class="mdc-tab-indicator mdc-tab-indicator--active">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php echo form_open(base_url('manage/' . $op['id'] . '/verify'), ['id' => 'op-data-form'], ['id' => $op['id'], 'redirect' => 'events']); ?>
                        <table class="mdc-data-table__table">
                            <tbody class="mdc-data-table__content">
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">ID</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['id']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Start time</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['start_time']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Event</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($event_types[$op['event']]); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Mission (Author)</td>
                                    <td class="mdc-data-table__cell">
                                        <?php echo html_escape($op['mission_name']); ?> (<?php echo html_escape($op['mission_author']); ?>) 
                                        <a target="_blank" title="OCAP" href="<?php echo OCAP_URL_PREFIX . rawurlencode($op['filename']); ?>"><img src="<?php echo base_url('public/ocap_logo.png'); ?>" alt="OCAP" class="ocap-link"></a>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell"><span title="Capture delay">Duration</span></td>
                                    <td class="mdc-data-table__cell"><span title="<?php echo $op['capture_delay']; ?>"><?php echo $duration_min; ?>m <?php echo $duration_sec; ?>s</span></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Players</td>
                                    <td class="mdc-data-table__cell">
                                        <p>
                                            <?php
                                            $pps = [];
                                            foreach ($op_sides as $s => $pc) {
                                                if ($pc > 0) {
                                                    $pps[] = '<span class="side__' . html_escape(strtolower($s)) . '">' . $sides[$s] . '</span> ' . $pc;
                                                }
                                            }
                                            ?>
                                            <?php echo $op['players_total']; ?>
                                            <small>(<?php echo implode(' + ', $pps); ?>)</small>
                                        </p>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Winner</td>
                                    <td class="mdc-data-table__cell"><?php print_end_winners($op['end_winner']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Updated</td>
                                    <td class="mdc-data-table__cell">
                                        <p>
                                            <?php echo gmdate('Y-m-d H:i:s', $op['updated']); ?>
                                            <br>
                                            <?php echo strtolower(timespan($op['updated'], '', 2)); ?> ago
                                        </p>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td colspan="2" class="mdc-data-table__cell">
                                        <div class="mdc-form-field">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="verified" name="verified" value="1" <?php echo $verified ? ' checked' : ''; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                            <label for="verified">All data verified <span class="material-icons verified-icon"><?php echo $verified ? 'verified' : 'new_releases'; ?></span></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td colspan="2" class="mdc-data-table__cell">
                                        <button type="submit" name="action" value="update" class="mdc-button mdc-button--raised mdc-button--leading">
                                            <span class="mdc-button__ripple"></span>
                                            <span class="mdc-button__focus-ring"></span>
                                            <i class="material-icons mdc-button__icon" aria-hidden="true">save</i>
                                            <span class="mdc-button__label">Save</span>
                                        </button>
                                        <button type="reset" name="action" value="reset" class="mdc-button mdc-button--outlined">
                                            <span class="mdc-button__ripple"></span>
                                            <span class="mdc-button__focus-ring"></span>
                                            <i class="material-icons mdc-button__icon" aria-hidden="true">cancel</i>
                                            <span class="mdc-button__label">Reset</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
if ($entity_id !== false) {
    $ent = $op_entities[array_search($entity_id, array_column($op_entities, 'id'))];
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' events for entity <span class="selected-entity">#' . $ent['id'] . ' <span class="side__' . html_escape(strtolower($ent['side'])) . '">' . $ent['name'] . '</span></span></div>';
} elseif ($player !== false) {
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' events for player <span class="selected-entity">#' . $player['id'] . ' ' . $player['name'] . '</span></div>';
} elseif (count($items) === 0) {
    echo '<div class="mdc-typography--body1 list__no_items">No events found...</div>';
} else {
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' events</div>';
}
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
                <div class="mdc-data-table mdc-elevation--z2" id="events-table">
                    <div class="mdc-data-table__table-container">

                        <div class="dnone" id="events-filters"></div>

                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="ascending" data-column-id="time">Time</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="attacker" title="Player name / Entity ID">Attacker</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="event">Event</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="victim" title="Player name / Entity ID">Victim</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="weapon">Weapon</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="distance">Distance</th>
                                    <?php if (!$verified) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="actions"></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php foreach ($items as $i) :
                                    $time = gmdate('H:i:s', $i['frame']);

                                    $attacker_name = html_escape($i['attacker_name']);
                                    $attacker_side_class = $i['attacker_side'] ? 'side__' . html_escape(strtolower($i['attacker_side'])) : '';
                                    $attacker_title = '';
                                    if ($i['attacker_id'] !== null) {
                                        $attacker_name = '<a href="' . base_url('manage/' . $op['id'] . '/events') . '?entity_id=' . $i['attacker_id'] . '">' . $attacker_name . '</a>';
                                        $attacker_title = ' title="#' . $i['attacker_id'] . '"';
                                    }

                                    $victim_name = html_escape($i['victim_name']);
                                    $victim_side_class = $i['victim_side'] ? 'side__' . html_escape(strtolower($i['victim_side'])) : '';
                                    $victim_title = '';
                                    if ($i['victim_id'] !== null) {
                                        $victim_name = '<a href="' . base_url('manage/' . $op['id'] . '/events') . '?entity_id=' . $i['victim_id'] . '">' . $victim_name . '</a>';
                                        $victim_title = ' title="#' . $i['victim_id'] . '"';
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
                                    <tr class="mdc-data-table__row" data-event-name="<?php echo html_escape($i['event']); ?>" data-event-id="<?php echo $i['id']; ?>" data-attacker-id="<?php echo $i['attacker_id']; ?>" data-victim-id="<?php echo $i['victim_id']; ?>">
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $time; ?></td>
                                        <td class="mdc-data-table__cell cell__title">
                                            <span class="<?php echo $attacker_side_class; ?>"<?php echo $attacker_title; ?>><?php echo $attacker_name; ?></span>
                                        </td>
                                        <td class="mdc-data-table__cell" data-sort="<?php echo html_escape($i['event']); ?>"><?php echo $event; ?></td>
                                        <td class="mdc-data-table__cell cell__title">
                                            <span class="<?php echo $victim_side_class; ?>"<?php echo $victim_title; ?>><?php echo $victim_name; ?></span>
                                        </td>
                                        <td class="mdc-data-table__cell"><span><?php echo html_escape($i['weapon']); ?></span></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo html_escape($i['distance']); ?>"><?php echo $distance; ?></td>
                                        <?php if (!$verified) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                                <?php if (in_array($i['event'], ['hit', 'killed'])) : ?>
                                                    <button type="button" class="mdc-icon-button edit-event-btn" title="Edit event" disabled="disabled">
                                                        <span class="mdc-icon-button__ripple"></span>
                                                        <span class="mdc-icon-button__focus-ring"></span>
                                                        <i class="material-icons mdc-icon-button__icon" aria-hidden="true">edit</i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="mdc-icon-button delete-event-btn" title="Delete event" disabled="disabled">
                                                    <span class="mdc-icon-button__ripple"></span>
                                                    <span class="mdc-icon-button__focus-ring"></span>
                                                    <i class="material-icons mdc-icon-button__icon" aria-hidden="true">delete_forever</i>
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>


    </div>
</div>

<script src="https://unpkg.com/slim-select@2.8.1/dist/slimselect.min.js"></script>
<script src="<?php echo base_url('public/events-filters.js'); ?>"></script>
<script>
    const entities = <?php echo json_encode($op_entities); ?>;
    const sides = <?php echo json_encode($sides); ?>;
    const weapons = Object.keys(getWeapons());

    function getWeapons() {
        const rows = document.querySelectorAll('#events-table tbody tr');

        const weapons_num = {};
        for (const tr of rows) {
            const weapon = tr.querySelector('td:nth-child(5) span').textContent.trim();
            if (weapons_num[weapon] === undefined) {
                weapons_num[weapon] = 1;
            } else {
                weapons_num[weapon] += 1;
            }
        }

        return weapons_num;
    }                         

    async function editEventAction(btn) {
        const btn_icon = btn.querySelector('i');
        const tr = btn.closest('tr');
        const event_id = tr.dataset.eventId;
        const event_name = tr.dataset.eventName;
        const attacker_td = tr.querySelector('td:nth-child(2)');
        const attacker_id = tr.dataset.attackerId;
        const victim_entity = entities.find(e => e.id === tr.dataset.victimId);
        const weapon_td = tr.querySelector('td:nth-child(5)');
        const weapon_span = weapon_td.querySelector('span');
        const weapon = weapon_span.textContent.trim();

        try {
            if (btn_icon.textContent === 'edit') {
                btn_icon.textContent = 'done';

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
                            value: ent.id,
                            selected: ent.id === attacker_id
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
                            value: ent.id,
                            selected: ent.id === attacker_id
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
                attacker_select.classList.add('edit-attacker-ss');
                attacker_td.appendChild(attacker_select);
                new SlimSelect({
                    select: attacker_select,
                    settings: {
                        showSearch: true,
                        allowDeselect: true
                    },
                    data: [
                        {
                            text: 'nobody / "something"',
                            placeholder: 'true',
                            value: ''
                        },
                        {
                            text: '#' + victim_entity.id + ' ' + victim_entity.name + ' (self inflicted)',
                            value: victim_entity.id
                        },
                        ...ss_entities_data_field
                    ]
                });

                const weapon_select = document.createElement('select');
                weapon_select.classList.add('edit-weapon-ss');
                weapon_td.appendChild(weapon_select);
                new SlimSelect({
                    select: weapon_select,
                    settings: {
                        showSearch: true,
                        allowDeselect: true
                    },
                    data: [
                        {
                            text: '(empty)',
                            placeholder: 'true',
                            value: ''
                        },
                        ...weapons.filter(w => w !== '').map(w => {
                            return {
                                text: w,
                                value: w,
                                selected: w === weapon
                            };
                        })
                    ],
                    events: {
                        addable: (value) => {
                            if (value.trim() === '' || weapons.includes(value.trim())) {
                                return false;
                            }

                            return value.trim();
                        }
                    }
                });
            } else {
                const attacker_select = attacker_td.querySelector('select.edit-attacker-ss');
                const weapon_select = weapon_td.querySelector('select.edit-weapon-ss');
                if (attacker_select && attacker_select.slim && weapon_select && weapon_select.slim) {
                    attacker_select.slim.close();
                    weapon_select.slim.close();

                    const form_data = new FormData();

                    const new_attacker_id = attacker_select.value;
                    if (new_attacker_id !== attacker_id) {
                        form_data.append('new_attacker_id', new_attacker_id);
                    }

                    const new_weapon = weapon_select.value;
                    if (new_weapon !== weapon) {
                        form_data.append('new_weapon', new_weapon);
                    }

                    btn.disabled = true;
                    if (Array.from(form_data).length > 0) {
                        form_data.append('action', 'edit-event');
                        form_data.append('operation_id', <?php echo $op['id']; ?>);
                        form_data.append('event_id', event_id);

                        const response = await fetch('<?php echo base_url('edit-event'); ?>', {
                            method: 'POST',
                            body: form_data,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const res_json = await response.json();
                            if (res_json.errors.length === 0) {
                                if (res_json.action === 'edit-event') {
                                    if (new_attacker_id !== attacker_id) {
                                        tr.dataset.attackerId = new_attacker_id;

                                        const span = attacker_td.querySelector('span');
                                        if (span) {
                                            span.textContent = '';
                                            span.className = '';
                                            span.title = '';
                                        }

                                        const new_attacker_entity = entities.find(e => e.id === new_attacker_id);
                                        if (new_attacker_entity) {
                                            span.className = 'side__' + String(new_attacker_entity.side).toLowerCase();
                                            span.title = '#' + new_attacker_entity.id;
                                            const a = document.createElement('a');
                                            a.textContent = new_attacker_entity.name;
                                            a.href = '<?php echo base_url('manage/' . $op['id'] . '/events'); ?>?entity_id=' + new_attacker_entity.id;
                                            span.appendChild(a);
                                        }
                                    } else if (new_weapon !== weapon) {
                                        if (!weapons.includes(new_weapon)) {
                                            weapons.unshift(new_weapon);
                                        }

                                        weapon_span.textContent = new_weapon;
                                    }
                                } else {
                                    throw new Error('Unknown response action 😵');
                                }
                            } else {
                                throw new Error('⚠ Errors:\n' + res_json.errors.join('\n'));
                            }
                        } else {
                            throw new Error('Response not ok 😔');
                        }
                    }

                    attacker_select.slim.destroy();
                    weapon_select.slim.destroy();
                } else {
                    throw new Error('SlimSelect instance not available');
                }

                attacker_select.remove();
                weapon_select.remove();
                btn_icon.textContent = 'edit';
                btn.disabled = false;
            }
        } catch (err) {
            console.error(err);
            alert(err.message || JSON.stringify(err));

            const attacker_select = attacker_td.querySelector('select.edit-attacker-ss');
            if (attacker_select) {
                if (attacker_select.slim) {
                    attacker_select.slim.destroy();
                }
                attacker_select.remove();
            }
            const weapon_select = weapon_td.querySelector('select.edit-weapon-ss');
            if (weapon_select) {
                if (weapon_select.slim) {
                    weapon_select.slim.destroy();
                }
                weapon_select.remove();
            }

            btn_icon.textContent = 'edit';
            btn.disabled = false;
        }
    }

    async function deleteEventAction(btn) {
        const tr = btn.closest('tr');
        const event_id = tr.dataset.eventId;
        const attacker_entity = entities.find(e => e.id === tr.dataset.attackerId);
        const attacker_string = attacker_entity ? '#' + attacker_entity.id + ' ' + attacker_entity.name : '';
        const victim_entity = entities.find(e => e.id === tr.dataset.victimId);
        const victim_string = victim_entity ? '#' + victim_entity.id + ' ' + victim_entity.name : '';
        const event_name = tr.dataset.eventName;

        const event_time = tr.querySelector('td:nth-child(1)').textContent.trim();

        const confirmation = confirm('🗑️ Deleting event: \n\n[' + event_time + '] ' + attacker_string + ' <' + event_name + '>  ' + victim_string + ' \n\nAre you sure?');
        if (!confirmation) return;

        const form_data = new FormData();
        form_data.append('action', 'delete-event');
        form_data.append('operation_id', <?php echo $op['id']; ?>);
        form_data.append('event_id', event_id);

        btn.disabled = true;
        try {
            const response = await fetch('<?php echo base_url('edit-event'); ?>', {
                method: 'POST',
                body: form_data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const res_json = await response.json();
                if (res_json.errors.length === 0) {
                    if (res_json.action === 'delete-event') {
                        btn.remove();
                        tr.style.opacity = '0.4';

                        const edit_attacker_btn = tr.querySelector('td .edit-attacker-btn');
                        if (edit_attacker_btn) {
                            edit_attacker_btn.remove();

                            const select = tr.querySelector('td select');
                            if (select) {
                                if (select.slim) {
                                    select.slim.destroy();
                                }
                                select.remove();
                            }
                        }
                    } else {
                        throw new Error('Unknown response action 😵');
                    }
                } else {
                    throw new Error('⚠ Errors:\n' + res_json.errors.join('\n'));
                }
            } else {
                throw new Error('Response not ok 😔');
            }
        } catch (err) {
            console.error(err);
            alert(err.message || JSON.stringify(err));
            btn.disabled = false;
        }
    }

    init_events_filters(entities, sides);

    const edit_btns = document.querySelectorAll('.edit-event-btn');
    for (const b of edit_btns) {
        b.addEventListener('click', (ev) => {
            ev.preventDefault();
            editEventAction(b);
        });
        b.disabled = false;
    }

    const delete_btns = document.querySelectorAll('.delete-event-btn');
    for (const b of delete_btns) {
        b.addEventListener('click', (ev) => {
            ev.preventDefault();
            deleteEventAction(b);
        });
        b.disabled = false;
    }
</script>