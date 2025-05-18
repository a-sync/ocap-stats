<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
$sides = $this->config->item('sides');

$year_prefix = '';
if ($year !== false) {
    $year_prefix = $year . '/';
}
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
        ?>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <table class="mdc-data-table__table">
                            <tbody class="mdc-data-table__content">
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">ID</td>
                                    <td class="mdc-data-table__cell"><?php echo $op['id']; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Date</td>
                                    <td class="mdc-data-table__cell"><?php echo $op['date']; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Start time</td>
                                    <td class="mdc-data-table__cell" data-ts="<?php echo $op['start_time']; ?>"><?php echo $op['start_time']; ?></td>
                                </tr>
                                <?php // TODO: in game time / timestamps ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Tag</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['tag']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Event</td>
                                    <td class="mdc-data-table__cell"><?php echo $event_types[$op['event']]; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Mission</td>
                                    <td class="mdc-data-table__cell">
                                        <?php echo html_escape($op['mission_name']); ?>
                                        <br>
                                        <span class="mdc-typography--caption">
                                            <a title="OCAP" target="_blank" href="<?php echo OCAP_URL_PREFIX . rawurlencode($op['filename']); ?>"><?php echo html_escape($op['filename']); ?></a>
                                        </span>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Map</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['world_name']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Author</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['mission_author']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Duration</td>
                                    <td class="mdc-data-table__cell"><?php echo $duration_min; ?> minutes</td>
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
                                    <td class="mdc-data-table__cell">End message</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['end_message']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Commanders</td>
                                    <td class="mdc-data-table__cell">
                                        <?php
                                        foreach ($op_commanders as $c) {
                                            $name_title = '';
                                            if ($c['entity_name'] !== $c['name']) {
                                                $name_title = ' title="' . html_escape($c['name']) . '"';
                                            }
                                            echo '<a href="' . base_url($year_prefix . 'player/') . $c['player_id'] . '"' . $name_title . '>' . html_escape($c['entity_name']) . '</a> (<span class="side__' . html_escape(strtolower($c['side'])) . '">' . $sides[$c['side']] . '</span>)<br>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>