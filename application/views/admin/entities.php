<?php defined('BASEPATH') or exit('No direct script access allowed');

$show_hit_data = (!defined('ADJUST_HIT_DATA') || ADJUST_HIT_DATA >= 0) ? true : false;
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
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/entities'); ?>" class="mdc-tab mdc-tab--active" role="tab" aria-selected="true" tabindex="7">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Entities</span>
                                            </span>
                                            <span class="mdc-tab-indicator mdc-tab-indicator--active">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/events'); ?>" class="mdc-tab" role="tab" aria-selected="false" tabindex="8">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Events</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php echo form_open(base_url('manage/' . $op['id'] . '/verify'), ['id' => 'op-data-form'], ['id' => $op['id'], 'redirect' => 'entities']); ?>
                        <table class="mdc-data-table__table">
                            <tbody class="mdc-data-table__content">
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">ID</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['id']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Start time</td>
                                    <td class="mdc-data-table__cell" data-ts="<?php echo $op['start_time']; ?>"><?php echo html_escape($op['start_time']); ?></td>
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
                                            <span data-ts="<?php echo gmdate('Y-m-d H:i:s', $op['updated']); ?>"><?php echo gmdate('Y-m-d H:i:s', $op['updated']); ?></span>
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
if (count($items) === 0) {
    echo '<div class="mdc-typography--body1 list__no_items">No entities found...</div>';
} else {
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' entities</div>';
}
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
                <div class="mdc-data-table mdc-elevation--z2" id="entities-table">
                    <div class="mdc-data-table__table-container">

                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="ascending" data-column-id="entity_id">ID</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="name" title="Player name / Asset class">Name</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="group">Group</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="role">Role</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="player_entities" title="Player entities">👥</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="join_time" title="Join time">🎬</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="time" title="Time in game">⏱️</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="distance" title="Distance traveled">🏃</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="shots" title="Shots">S</th>
                                    <?php if ($show_hit_data) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="hits" title="Hits">H</th>
                                    <?php endif; ?>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="kills" title="Kills">K</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="deaths" title="Deaths">D</th>
                                    <?php if ($show_hit_data) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fhits" title="Friendly fire">FF</th>
                                    <?php endif; ?>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fkills" title="Teamkills">Tk</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="vkills" title="Destroyed assets">DA</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="sus" title="Sus factor">ඞ</th>
                                    <?php if (!$verified) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="actions"></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php foreach ($items as $index => $i) :
                                    $role = $i['role'];
                                    $group = $i['group_name'] === '' ? $sides[$i['side']] : $i['group_name'];
                                    if (strpos($role, '@') !== false) {
                                        $role_group_arr = explode('@', $i['role']);
                                        if (isset($role_group_arr[0]) && $role_group_arr[0] !== '') {
                                            $role = $role_group_arr[0];
                                        }
                                        if (isset($role_group_arr[1]) && $role_group_arr[1] !== '') {
                                            $group = $role_group_arr[1];
                                        }
                                    }

                                    $name = '<a href="' . base_url('manage/' . $op['id'] . '/events') . '?entity_id=' . $i['id'] . '">' . html_escape($i['name']) . '</a>';
                                    $pname_or_class = 'n/a';
                                    if ($i['player_id']) {
                                        $pname_or_class = $i['player_name'];
                                    } elseif ($i['class']) {
                                        $pname_or_class = $i['class'];
                                    }

                                    $distance = 'n/a';
                                    if ($i['distance_traveled'] > 0) {
                                        $distance = $i['distance_traveled'] . ' m';
                                    }

                                    $join_time = get_timestamp($i['join_time_seconds']);

                                    $time = 'n/a';
                                    if ($i['seconds_in_game'] > 0) {
                                        $time = get_timestamp($i['seconds_in_game']);
                                    }

                                    $hits = $i['hits'];
                                    if (defined('ADJUST_HIT_DATA') && $op['id'] < ADJUST_HIT_DATA) {
                                        $hits = '';
                                    }

                                    $fhits = $i['fhits'];
                                    if (defined('ADJUST_HIT_DATA') && $op['id'] < ADJUST_HIT_DATA) {
                                        $fhits = '';
                                    }

                                    $medals = [];
                                    if (isset($players_first_op[$i['player_id']]) && $players_first_op[$i['player_id']] === $op['id']) {
                                        $medals[] = '<span>👶</span>';
                                    }
                                    if (isset($op_commanders[$i['side']]) && $op_commanders[$i['side']]['entity_id'] === $i['id']) {
                                        $medals[] = '<span class="side__' . html_escape(strtolower($i['side'])) . '">🎖</span>';
                                    }

                                    $player_entities_num = count($i['player_entities']);
                                    $highlight = '';
                                    if ($player_entities_num > 1) {
                                        $highlight = ' onmouseover="highlightPlayer(' . $i['player_id'] . ')" onmouseout="highlightPlayer()"';
                                    }

                                    $player_entities = strval($player_entities_num);
                                    if ($player_entities_num > 1) {
                                        $player_entities = '<a href="' . base_url('manage/' . $op['id'] . '/events') . '?player_id=' . $i['player_id'] . '">' . $player_entities_num . '</a>';
                                    }
                                ?>
                                    <tr class="mdc-data-table__row" data-entity-id="<?php echo $i['id']; ?>" data-player-id="<?php echo $i['player_id']; ?>"<?php echo $highlight; ?>>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['id']; ?></td>
                                        <td class="mdc-data-table__cell cell__title">
                                            <span title="<?php echo html_escape($pname_or_class); ?>"><?php echo $name; ?></span><?php echo implode(' ', $medals); ?>
                                        </td>
                                        <td class="mdc-data-table__cell side__<?php echo html_escape(strtolower($i['side'])); ?>"><?php echo html_escape($group); ?></td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($role); ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                            <span title="<?php echo html_escape(implode(', ', $i['player_entities'])); ?>"><?php echo $player_entities; ?></span>
                                        </td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $join_time; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo intval($i['seconds_in_game']); ?>"><?php echo $time; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo intval($i['distance_traveled']); ?>"><?php echo $distance; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['shots']; ?></td>
                                        <?php if ($show_hit_data) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $hits; ?></td>
                                        <?php endif; ?>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['kills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['deaths']; ?></td>
                                        <?php if ($show_hit_data) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $fhits; ?></td>
                                        <?php endif; ?>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['fkills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['vkills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $i['sus_factor']; ?>"><?php echo number_format($i['sus_factor'], 0, '.', ''); ?></td>
                                        <?php if (!$verified && $i['is_player']) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                                <button type="button" class="mdc-icon-button not-a-player-btn" title="Not a player" disabled="disabled">
                                                    <span class="mdc-icon-button__ripple"></span>
                                                    <span class="mdc-icon-button__focus-ring"></span>
                                                    <i class="material-icons mdc-icon-button__icon" aria-hidden="true">person_off</i>
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

<script>
    function highlightPlayer(playerId) {
        const highlighted = document.querySelectorAll('#entities-table tbody tr.mdc-data-table__row--selected');
        for (const tr of highlighted) {
            tr.classList.remove('mdc-data-table__row--selected');
        }

        if (playerId) {
            const players_entities = document.querySelectorAll('#entities-table tbody tr[data-player-id="' + playerId + '"]');
            for (const tr of players_entities) {
                tr.classList.add('mdc-data-table__row--selected');
            }
        }
    }

    async function notAPlayerAction(btn) {
        const tr = btn.closest('tr');
        const entity_id = tr.dataset.entityId;

        const form_data = new FormData();
        form_data.append('action', 'not-a-player');
        form_data.append('operation_id', <?php echo $op['id']; ?>);
        form_data.append('entity_id', entity_id);

        const entity_name = tr.querySelector('td:nth-child(2) span').textContent.trim();
        const entity_group = tr.querySelector('td:nth-child(3)').textContent.trim();
        const entity_role = tr.querySelector('td:nth-child(4)').textContent.trim();
        const confirmation = confirm('🙅‍♂️ Removing is_player & player_id fields of entity: \n\n#' + entity_id + ' ' + entity_name + '  (' + entity_role + '@' + entity_group + ') \n\nAre you sure?');
        if (!confirmation) return;

        btn.disabled = true;
        try {
            const response = await fetch('<?php echo base_url('edit-entity'); ?>', {
                method: 'POST',
                body: form_data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const res_json = await response.json();
                if (res_json.errors.length === 0) {
                    if (res_json.action === 'not-a-player') {
                        btn.remove();
                        tr.style.opacity = '0.4';
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

    const domLoaded = () => {
        console.log('DOM loaded');

        const edit_btns = document.querySelectorAll('.not-a-player-btn');
        for (const b of edit_btns) {
            b.addEventListener('click', (ev) => {
                ev.preventDefault();
                notAPlayerAction(b);
            });
            b.disabled = false;
        }
    };

    if (document.readyState === 'complete' ||
        (document.readyState !== 'loading' && !document.documentElement.doScroll)) domLoaded();
    else document.addEventListener('DOMContentLoaded', domLoaded);
</script>