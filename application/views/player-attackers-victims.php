<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (count($items) === 0) {
    echo '<div class="mdc-typography--body1 list__no_items">No ' . $tab . ' found...</div>';
} else {
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' ' . $tab . '</div>';
}

$show_hit_data = (!defined('ADJUST_HIT_DATA') || ADJUST_HIT_DATA >= 0) ? true : false;
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

                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="name" title="Asset class">Name</th>
                                <?php if ($show_hit_data) : ?>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="hits" title="Hits">H</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="avg_hit_dist" title="Average hit distance">μH</th>
                                <?php endif; ?>
                                <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="kills" title="Kills">K</th>
                                <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="avg_kill_dist" title="Average kill distance">μK</th>
                                <?php if ($show_hit_data) : ?>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fhits" title="Friendly fire">FF</th>
                                <?php endif; ?>
                                <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fkills" title="Teamkills">Tk</th>

                            </tr>
                        </thead>
                        <tbody class="mdc-data-table__content">
                            <?php
                            foreach ($items as $index => $i) :
                                $name = html_escape($i['name']);
                                if (isset($i['player_id']) && !is_null($i['player_id'])) {
                                    $name = '<a href="' . base_url('player/') . $i['player_id'] . '">' . $name . '</a>';
                                } else {
                                    $name = '<span title="' . html_escape($i['class']) . '">' . $name . '</span>';
                                }

                                if ($i['type'] !== 'unit') {
                                    $i['fhits'] = '';
                                    $i['fkills'] = '';
                                }

                                $avg_hit_dist = '';
                                if ($i['hits'] > 0) {
                                    $avg_hit_dist = round($i['avg_hit_dist']) . ' m';
                                }

                                $avg_kill_dist = '';
                                if ($i['kills'] > 0) {
                                    $avg_kill_dist = round($i['avg_kill_dist']) . ' m';
                                }
                            ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell"><?php echo $name; ?></td>
                                    <?php if ($show_hit_data) : ?>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['hits']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $i['avg_hit_dist']; ?>"><?php echo $avg_hit_dist; ?></td>
                                    <?php endif; ?>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['kills']; ?></td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo $i['avg_kill_dist']; ?>"><?php echo $avg_kill_dist; ?></td>
                                    <?php if ($show_hit_data) : ?>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['fhits']; ?></td>
                                    <?php endif; ?>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['fkills']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>