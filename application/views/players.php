<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (count($items) === 0) :
    echo '<div class="mdc-typography--body1 list__no_items">No players matching the selected filters...</div>';
else :
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' players</div>';

    $show_hit_data = (!defined('ADJUST_HIT_DATA') || ADJUST_HIT_DATA >= 0) ? true : false;
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="name">Name</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="shots">Shots</th>
                                    <?php /* <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="adj_shots">Adjusted shots</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="hits">Hits</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="adj_hits">Adjusted hits</th> */ ?>
                                    <?php if ($show_hit_data) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="hits_shots_ratio" title="Hits / Shots (Shots / Hits)">Hit % (S/H)</th>
                                    <?php endif; ?>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="kills">Kills</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="kills_shots_ratio" title="Kills / Shots (Shots / Kills)">Kill % (S/K)</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="ascending" data-column-id="deaths">Deaths</th>
                                    <?php if ($show_hit_data) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fhits" title="Friendly fire">FF</th>
                                    <?php endif; ?>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fkills" title="Teamkills">Tk</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="vkills" title="Destroyed asse<?php echo (mt_rand(0, 99) ? 't' : '') ?>s">DA</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="attendance" title="Attendance (Kills / Attendance)">A (K/A)</th>
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
                                    $fhits = '';

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
                                    if ($i['adj_fhits'] !== false) {
                                        $fhits = $i['adj_fhits'];
                                    }

                                    $kills_shots_ratio_raw = 0;
                                    if ($i['shots'] > 0) {
                                        $kills_shots_ratio = number_format($i['kills'] / $i['shots'] * 100, 2) . '%';
                                        $kills_shots_ratio_raw = $i['kills'] / $i['shots'];
                                    }
                                    if ($i['kills'] > 0) {
                                        $shots_kills_ratio = number_format($i['shots'] / $i['kills'], 1);
                                    }

                                    if ($i['attendance'] > 0) {
                                        $kills_attendance_ratio = number_format($i['kills'] / $i['attendance'], 1);
                                    }

                                    $name = '<a href="' . base_url('player/') . $i['id'] . '">' . html_escape($i['name']) . '</a>';
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell cell__title"><?php echo $name; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['shots']; ?></td>
                                        <?php /* <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['adj_shots']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['hits']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['adj_hits']; ?></td> */ ?>
                                        <?php if ($show_hit_data) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $hits_shots_ratio_raw; ?>"><?php echo $hits_shots_ratio; ?><?php echo $shots_hits_ratio ? ' (' . $shots_hits_ratio . ')' : ''; ?></td>
                                        <?php endif; ?>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['kills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $kills_shots_ratio_raw; ?>"><?php echo $kills_shots_ratio; ?> (<?php echo $shots_kills_ratio; ?>)</td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['deaths']; ?></td>
                                        <?php if ($show_hit_data) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $fhits; ?></td>
                                        <?php endif; ?>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['fkills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['vkills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $kills_attendance_ratio; ?>"><?php echo $i['attendance']; ?> (<?php echo $kills_attendance_ratio; ?>)</td>
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