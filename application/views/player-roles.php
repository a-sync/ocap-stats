<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (count($items) === 0) :
    echo '<div class="mdc-typography--body1 list__no_items">No roles found...</div>';
else :
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' roles</div>';

    $sides = $this->config->item('sides');
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">

                        <?php echo $player_menu; ?>

                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">

                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="name" title="Name">Role</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="shots" title="Shots">S</th>
                                    <?php /*<th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="adj_shots" title="Adjusted shots">S*</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="hits" title="Hits">H</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="adj_hits" title="Adjusted hits">H*</th> */ ?>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="hits_shots_ratio" title="Hits / Shots (Shots / Hits)">H% (S/H)</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="kills" title="Kills">K</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="kills_shots_ratio" title="Kills / Shots (Shots / Kills)">K% (S/K)</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="deaths" title="Deaths">D</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fhits" title="Friendly fire">FF</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fkills" title="Teamkills">Tk</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="vkills" title="Destroyed assets">DA</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="west_count"><?php echo $sides['WEST']; ?></th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="east_count"><?php echo $sides['EAST']; ?></th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="guer_count"><?php echo $sides['GUER']; ?></th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="total_count" title="Attendance (Kills / Attendance)">A (K/A)</th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php
                                foreach ($items as $index => $i) :
                                    $hits_shots_ratio = '0.00%';
                                    $kills_shots_ratio = '0.00%';
                                    $shots_kills_ratio = '0.0';
                                    $shots_hits_ratio = '0.0';
                                    $kills_attendance_ratio = '0.0';

                                    $hits_shots_ratio_raw = 0;
                                    if ($i['adj_shots'] === false) {
                                        $hits_shots_ratio = '';
                                    } elseif ($i['adj_shots'] > 0) {
                                        $hits_shots_ratio = number_format(intval($i['adj_hits']) / $i['adj_shots'] * 100, 2) . '%';
                                        $hits_shots_ratio_raw = $i['adj_hits'] / $i['adj_shots'];
                                    }
                                    if ($i['adj_hits'] === false) {
                                        $shots_hits_ratio = '';
                                    } elseif ($i['adj_hits'] > 0) {
                                        $shots_hits_ratio = number_format(intval($i['adj_shots']) / $i['adj_hits'], 1);
                                    }

                                    $kills_shots_ratio_raw = 0;
                                    if ($i['shots'] > 0) {
                                        $kills_shots_ratio = number_format($i['kills'] / $i['shots'] * 100, 2) . '%';
                                        $kills_shots_ratio_raw = $i['kills'] / $i['shots'];
                                    }
                                    if ($i['kills'] > 0) {
                                        $shots_kills_ratio = number_format($i['shots'] / $i['kills'], 1);
                                    }

                                    if ($i['total_count'] > 0) {
                                        $kills_attendance_ratio = number_format($i['kills'] / $i['total_count'], 1);
                                    }
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell"><?php echo html_escape($i['role_name']); ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['shots']; ?></td>
                                        <?php /* <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['adj_shots']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['hits']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['adj_hits']; ?></td> */ ?>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $hits_shots_ratio_raw; ?>"><?php echo $hits_shots_ratio; ?><?php echo $shots_hits_ratio ? ' (' . $shots_hits_ratio . ')' : ''; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['kills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $kills_shots_ratio_raw; ?>"><?php echo $kills_shots_ratio; ?> (<?php echo $shots_kills_ratio; ?>)</td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['deaths']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['fhits']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['fkills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['vkills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['west_count']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['east_count']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['guer_count']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $kills_attendance_ratio; ?>"><?php echo $i['total_count']; ?> (<?php echo $kills_attendance_ratio; ?>)</td>
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