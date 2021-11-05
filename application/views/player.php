<?php defined('BASEPATH') or exit('No direct script access allowed');

$show_hit_data = (!defined('ADJUST_HIT_DATA') || ADJUST_HIT_DATA >= 0) ? true : false;

$hits_shots_ratio = '0.00%';
$kills_shots_ratio = '0.00%';
$shots_kills_ratio = '0.0';
$shots_hits_ratio = '0.0';
$fhits = '';

if ($player['adj_shots'] === false) {
    $hits_shots_ratio = '';
} elseif ($player['adj_shots'] > 0) {
    $hits_shots_ratio = number_format(intval($player['adj_hits']) / $player['adj_shots'] * 100, 2) . '%';
}
if ($player['adj_hits'] === false) {
    $shots_hits_ratio = '';
} elseif ($player['adj_hits'] > 0) {
    $shots_hits_ratio = number_format(intval($player['adj_shots']) / $player['adj_hits'], 1);
}
if ($player['adj_fhits'] !== false) {
    $fhits = $player['adj_fhits'];
}

if ($player['shots'] > 0) {
    $kills_shots_ratio = number_format($player['kills'] / $player['shots'] * 100, 2) . '%';
}
if ($player['kills'] > 0) {
    $shots_kills_ratio = number_format($player['shots'] / $player['kills'], 1);
}

if ($player['attendance'] > 0) {
    $kills_attendance_ratio = number_format($player['kills'] / $player['attendance'], 1);
}

$aliases_names = array_map('html_escape', array_column($aliases, 'name'));
?>

<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">


        <?php if (count($errors) > 0) : ?>
            <div class="errors mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <h3>⚠️ Errors</h3>
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <?php if ($player) : ?>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <table class="mdc-data-table__table">
                            <tbody class="mdc-data-table__content">
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">ID</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['id']; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Name</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($player['name']); ?></td>
                                </tr>
                                <?php if (count($aliases) > 0) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Aliases</td>
                                        <td class="mdc-data-table__cell"><?php echo implode(' <br>', $aliases_names); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Shots</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['shots']; ?></td>
                                </tr>
                                <!-- <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Adjusted shots</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['adj_shots']; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Hits</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['hits']; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Adjusted hits</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['adj_hits']; ?></td>
                                </tr> -->
                                <?php if ($show_hit_data) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Hits / Shots (Shots / Hits)</td>
                                        <td class="mdc-data-table__cell"><?php echo $hits_shots_ratio; ?><?php echo $shots_hits_ratio ? ' (' . $shots_hits_ratio . ')' : ''; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Kills</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['kills']; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Kills / Shots (Shots / Kills)</td>
                                    <td class="mdc-data-table__cell"><?php echo $kills_shots_ratio; ?> (<?php echo $shots_kills_ratio; ?>)</td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Deaths</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['deaths']; ?></td>
                                </tr>

                                <?php if ($show_hit_data) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Friendly fire</td>
                                        <td class="mdc-data-table__cell"><?php echo $fhits; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Teamkills</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['fkills']; ?></td>
                                </tr>

                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Destroyed assets</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['vkills']; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Attendance (Kills / Attendance)</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['attendance']; ?> (<?php echo $kills_attendance_ratio; ?>)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>