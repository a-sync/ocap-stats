<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (count($items) === 0) :
    echo '<div class="mdc-typography--body1 list__no_items">No commanders matching the selected filters...</div>';
else :
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' commanders</div>';

    $sides = $this->config->item('sides');
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
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="win">Victories</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="loss">Defeats</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="wl_west" title="Wins / Losses"><?php echo $sides['WEST']; ?></th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="wl_east" title="Wins / Losses"><?php echo $sides['EAST']; ?></th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="wl_guer" title="Wins / Losses"><?php echo $sides['GUER']; ?></th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php
                                foreach ($items as $i) :
                                    $name = '<a href="' . base_url('player/') . $i['player_id'] . '">' . html_escape($i['name']) . '</a>';

                                    $blufor_wl_ratio = 0;
                                    if ($i['WEST']['win'] === 0) {
                                        $blufor_wl_ratio = 0 - $i['WEST']['loss'];
                                    } elseif ($i['WEST']['loss'] === 0) {
                                        $blufor_wl_ratio = $i['WEST']['win'];
                                    } else {
                                        $blufor_wl_ratio = $i['WEST']['win'] / $i['WEST']['loss'];
                                    }
                                    $opfor_wl_ratio = 0;
                                    if ($i['EAST']['win'] === 0) {
                                        $opfor_wl_ratio = 0 - $i['EAST']['loss'];
                                    } elseif ($i['EAST']['loss'] === 0) {
                                        $opfor_wl_ratio = $i['EAST']['win'];
                                    } else {
                                        $opfor_wl_ratio = $i['EAST']['win'] / $i['EAST']['loss'];
                                    }
                                    $ind_wl_ratio = 0;
                                    if ($i['GUER']['win'] === 0) {
                                        $ind_wl_ratio = 0 - $i['GUER']['loss'];
                                    } elseif ($i['GUER']['loss'] === 0) {
                                        $ind_wl_ratio = $i['GUER']['win'];
                                    } else {
                                        $ind_wl_ratio = $i['GUER']['win'] / $i['GUER']['loss'];
                                    }
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell cell__title"><?php echo $name; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['win_total']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['loss_total']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $blufor_wl_ratio; ?>"><?php echo $i['WEST']['win']; ?> / <?php echo $i['WEST']['loss']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $opfor_wl_ratio; ?>"><?php echo $i['EAST']['win']; ?> / <?php echo $i['EAST']['loss']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $ind_wl_ratio; ?>"><?php echo $i['GUER']['win']; ?> / <?php echo $i['GUER']['loss']; ?></td>
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