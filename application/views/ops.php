<?php
defined('BASEPATH') or exit('No direct script access allowed');

$year_prefix = '';
$year_text = '';
if ($year !== false) {
    $year_prefix = $year . '/';
    $year_text = ' in ' . $year;
}

if (count($items) === 0) :
    echo '<div class="mdc-typography--body1 list__no_items">No ops matching the selected filters' . $year_text . '...</div>';
else :
    echo '<div class="mdc-typography--caption list__total">' . count($items) . ' ops' . $year_text . '</div>';

    $event_types = $this->config->item('event_types');
    $sides = $this->config->item('sides');
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="id">ID</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="start_time" title="Date">Start time</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="event" title="Tag">Event</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="mission" title="Map">Mission</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="author">Author</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="duration">Duration</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="players">Players</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="winner" title="End message">Winner</th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php foreach ($items as $i) :
                                    $duration_min = floor(intval($i['mission_duration']) / 60);
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                            <a href="<?php echo base_url($year_prefix . 'op/') . $i['id']; ?>"><?php echo $i['id']; ?></a>
                                        </td>
                                        <td class="mdc-data-table__cell"><span title="<?php echo $i['date']; ?>" data-ts="<?php echo $i['start_time']; ?>"><?php echo $i['start_time']; ?></span></td>
                                        <td class="mdc-data-table__cell"><span title="<?php echo html_escape($i['tag']); ?>"><?php echo $event_types[$i['event']]; ?></span></td>
                                        <td class="mdc-data-table__cell cell__title">
                                            <span title="<?php echo html_escape($i['world_name']); ?>">
                                                <?php echo html_escape($i['mission_name']); ?>&nbsp;<sup class="mdc-typography--caption"><a target="_blank" title="OCAP" href="<?php echo OCAP_URL_PREFIX . rawurlencode($i['filename']); ?>"><img src="<?php echo base_url('public/ocap_logo.png'); ?>" alt="OCAP" class="ocap-link"></a></sup>
                                            </span>
                                        </td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($i['mission_author']); ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric" data-sort="<?php echo intval($i['mission_duration']); ?>"><?php echo $duration_min; ?>m</td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['players_total']; ?></td>
                                        <td class="mdc-data-table__cell"><?php print_end_winners($i['end_winner'], $i['end_message']); ?></td>
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