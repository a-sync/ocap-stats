<?php
defined('BASEPATH') or exit('No direct script access allowed');

$sides = $this->config->item('sides');

$year_prefix = '';
if ($year !== false) {
    $year_prefix = $year . '/';
}

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
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
            <div class="mdc-data-table mdc-elevation--z2" id="events-table">
                <div class="mdc-data-table__table-container">

                    <?php echo $op_menu; ?>

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
                            </tr>
                        </thead>
                        <tbody class="mdc-data-table__content">
                            <?php foreach ($deduped_items as $index => $i) :
                                $time = gmdate('H:i:s', $i['frame']);

                                $attacker_player_name = is_null($i['attacker_player_name']) ? '' : $i['attacker_player_name'];
                                $attacker_name = html_escape($i['attacker_name']);
                                $attacker_side_class = $i['attacker_side'] ? 'side__' . html_escape(strtolower($i['attacker_side'])) : '';
                                $attacker_title = '';
                                if ($i['attacker_player_id']) {
                                    $attacker_name = '<a href="' . base_url($year_prefix . 'player/') . $i['attacker_player_id'] . '">' . $attacker_name . '</a>';
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
                                    $victim_name = '<a href="' . base_url($year_prefix . 'player/') . $i['victim_player_id'] . '">' . $victim_name . '</a>';
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
<script src="<?php echo base_url('public/events-filters.js'); ?>"></script>
<script>
    const entities = <?php echo json_encode($op_entities); ?>;
    const sides = <?php echo json_encode($sides); ?>;

    const domLoaded = () => {
        console.log('DOM loaded');
        init_events_filters(entities, sides);
    };

    if (document.readyState === 'complete' ||
        (document.readyState !== 'loading' && !document.documentElement.doScroll)) domLoaded();
    else document.addEventListener('DOMContentLoaded', domLoaded);
</script>