<?php defined('BASEPATH') or exit('No direct script access allowed');

$show_hit_data = (!defined('ADJUST_HIT_DATA') || ADJUST_HIT_DATA >= 0) ? true : false;
?>

<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">

        <?php if (count($errors) > 0) : ?>
            <div class="errors mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <h3>⚠️ Errors</h3>
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <?php if ($player) :
            $hits_shots_ratio = '0.00%';
            $kills_shots_ratio = '0.00%';
            $shots_kills_ratio = '0.0';
            $shots_hits_ratio = '0.0';
            $fhits = '';
            $kills_deaths_ratio = '';

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

            $kills_deaths_ratio_raw = 0;
            if ($player['deaths'] > 0) {
                $kills_deaths_ratio_raw = $player['kills'] / $player['deaths'];
                $kills_deaths_ratio = number_format($kills_deaths_ratio_raw, 1);
            }

            if ($player['attendance'] > 0) {
                $kills_attendance_ratio = number_format($player['kills'] / $player['attendance'], 1);
            }

            $aliases_names = array_map('html_escape', array_column($aliases, 'name'));

            $distance_total = 'n/a';
            if ($player['distance_traveled'] > 1000) {
                $distance_total = number_format($player['distance_traveled'] / 1000, 3) . ' km';
            } elseif ($player['distance_traveled'] > 0) {
                $distance_total = number_format($player['distance_traveled']) . ' meters';
            }

            $time_total = 'n/a';
            if ($player['seconds_in_game'] > 0) {
                $time_total = strtolower(timespan(0, intval($player['seconds_in_game'])));
            }
        ?>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center">
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
                                        <td class="mdc-data-table__cell">
                                            <p><?php echo implode(' <br>', $aliases_names); ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($year !== false) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Season</td>
                                        <td class="mdc-data-table__cell"><?php echo $year; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (count($commanded_ops) > 0) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Commander</td>
                                        <td class="mdc-data-table__cell">x<?php echo count($commanded_ops); ?></td>
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
                                    <td class="mdc-data-table__cell">Deaths (Kills / Deaths)</td>
                                    <td class="mdc-data-table__cell"><?php echo $player['deaths']; ?><?php echo $kills_deaths_ratio ? ' (' . $kills_deaths_ratio . ')' : ''; ?></td>
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
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Total distance traveled</td>
                                    <td class="mdc-data-table__cell"><?php echo $distance_total; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Total time in game</td>
                                    <td class="mdc-data-table__cell"><?php echo $time_total; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>