<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (count($items) === 0) :
    echo '<div class="mdc-typography--body1 list__no_items">No events found...</div>';
else :
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' events</div>';
    //echo '<pre>'.print_r($items, true).'</pre>';

    $event_types = $this->config->item('event_types');
    $sides = $this->config->item('sides');
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">

                        <?php echo $op_menu; ?>

                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="ascending" data-column-id="time">Time</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="attacker" title="Player name">Attacker</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="event">Event</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="victim" title="Player name">Victim</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="weapon">Weapon</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="distance">Distance</th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php foreach ($items as $index => $i) : 
                                    $time = date('H:i:s', $i['frame']);

                                    $victim_player_name = is_null($i['victim_player_name']) ? '' : $i['victim_player_name'];
                                    $victim_name = html_escape($i['victim_name']);
                                    $victim_side_class = $i['victim_side'] ? 'side__'.html_escape(strtolower($i['victim_side'])) : '';
                                    $victim_title = '';
                                    if ($i['victim_player_id']) {
                                        $victim_name = '<a href="'.base_url('player/').$i['victim_player_id'].'">'.$victim_name.'</a>';
                                        if ($victim_player_name !== '' && $i['victim_name'] !== $victim_player_name) {
                                            $victim_title = ' title="'.html_escape($victim_player_name).'"';
                                        }
                                    }

                                    $attacker_player_name = is_null($i['attacker_player_name']) ? '' : $i['attacker_player_name'];
                                    $attacker_name = html_escape($i['attacker_name']);
                                    $attacker_side_class = $i['attacker_side'] ? 'side__'.html_escape(strtolower($i['attacker_side'])) : '';
                                    $attacker_title = '';
                                    if ($i['attacker_player_id']) {
                                        $attacker_name = '<a href="'.base_url('player/').$i['attacker_player_id'].'">'.$attacker_name.'</a>';
                                        if ($attacker_player_name !== '' && $i['attacker_name'] !== $attacker_player_name) {
                                            $attacker_title = ' title="'.html_escape($attacker_player_name).'"';
                                        }
                                    }

                                    $distance = '';
                                    if ($i['distance'] > 0) {
                                        $distance = html_escape($i['distance']).'m';
                                    }
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $time; ?></td>
                                        <td class="mdc-data-table__cell cell__title <?php echo $attacker_side_class; ?>">
                                            <span<?php echo $attacker_title; ?>><?php echo $attacker_name; ?></span>
                                        </td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($i['event']); ?></td>
                                        <td class="mdc-data-table__cell cell__title <?php echo $victim_side_class; ?>">
                                            <span<?php echo $victim_title; ?>><?php echo $victim_name; ?></span>
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

<?php endif; ?>
