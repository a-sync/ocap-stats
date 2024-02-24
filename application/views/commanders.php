<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (count($items) === 0) :
    echo '<div class="mdc-typography--body1 list__no_items">No commanders matching the selected filters...</div>';
else :
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' commanders</div>';

    $sides = $this->config->item('sides');
    $item0 = $items[0];
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="name">Name</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="win">Victories</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="loss">Defeats</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="draw">Ties</th>
                                    <?php if (isset($item0['WEST'])) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="wl_west" title="Wins / Losses"><?php echo $sides['WEST']; ?></th>
                                    <?php endif; ?>
                                    <?php if (isset($item0['EAST'])) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="wl_east" title="Wins / Losses"><?php echo $sides['EAST']; ?></th>
                                    <?php endif; ?>
                                    <?php if (isset($item0['GUER'])) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="wl_guer" title="Wins / Losses"><?php echo $sides['GUER']; ?></th>
                                    <?php endif; ?>
                                    <?php if (isset($item0['CIV'])) : ?>
                                        <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="wl_civ" title="Wins / Losses"><?php echo $sides['CIV']; ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php
                                foreach ($items as $i) :
                                    $name = '<a href="' . base_url('player/') . $i['player_id'] . '">' . html_escape($i['name']) . '</a>';

                                    $side_ratios = [];
                                    foreach ($sides as $s => $n) {
                                        $side_ratios[$s] = 0;
                                        if (isset($i[$s])) {
                                            if ($i[$s]['win'] === 0) {
                                                $side_ratios[$s] = 0 - $i[$s]['loss'];
                                            } elseif ($i[$s]['loss'] === 0) {
                                                $side_ratios[$s] = $i[$s]['win'];
                                            } else {
                                                $side_ratios[$s] = $i[$s]['win'] / $i[$s]['loss'];
                                            }
                                        }
                                    }
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell cell__title"><?php echo $name; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['win_total']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['loss_total']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['draw_total']; ?></td>
                                        <?php if (isset($i['WEST'])) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $side_ratios['WEST']; ?>"><?php echo $i['WEST']['win']; ?> / <?php echo $i['WEST']['loss']; ?></td>
                                        <?php endif; ?>
                                        <?php if (isset($i['EAST'])) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $side_ratios['EAST']; ?>"><?php echo $i['EAST']['win']; ?> / <?php echo $i['EAST']['loss']; ?></td>
                                        <?php endif; ?>
                                        <?php if (isset($i['GUER'])) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $side_ratios['GUER']; ?>"><?php echo $i['GUER']['win']; ?> / <?php echo $i['GUER']['loss']; ?></td>
                                        <?php endif; ?>
                                        <?php if (isset($i['CIV'])) : ?>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $side_ratios['CIV']; ?>"><?php echo $i['CIV']['win']; ?> / <?php echo $i['CIV']['loss']; ?></td>
                                        <?php endif; ?>
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