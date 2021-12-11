<?php defined('BASEPATH') or exit('No direct script access allowed');

$errors[] = '🚧 WIP'; //debug

$event_types = $this->config->item('event_types');
$sides = $this->config->item('sides');

$warn_icon = '<span class="material-icons">warning</span>';
$flaky_icon = '<span class="material-icons">flaky</span>';
$fixed_icon = '<span class="material-icons">check</span>';

$extra_attr = '';

function print_hq_entity_info($entity)
{
    $title = '';
    if ($entity['entity_name'] !== $entity['name']) {
        $title = ' title="' . html_escape($entity['name']) . '"';
    }
    echo '<span' . $title . '>' . html_escape($entity['entity_name']) . '</span>';
    $grp_rl = [];
    if ($entity['group_name'] !== '') {
        $grp_rl[] = html_escape($entity['group_name']);
    }
    if ($entity['role'] !== '') {
        $grp_rl[] = html_escape($entity['role']);
    }
    if (count($grp_rl) > 0) {
        echo ' <sup>(' . implode(' / ', $grp_rl) . ')</sup>';
    }
    echo '<br>';
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
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <div class="mdc-tab-bar">
                            <div class="mdc-tab-scroller">
                                <div class="mdc-tab-scroller__scroll-area">
                                    <div class="mdc-tab-scroller__scroll-content">
                                        <a href="<?php echo base_url('manage/' . $op['id']); ?>" class="mdc-tab" role="tab" aria-selected="true" tabindex="5">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Process data</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('fix-data/' . $op['id']); ?>" class="mdc-tab mdc-tab--active" role="tab" aria-selected="false" tabindex="6">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Verify data</span>
                                            </span>
                                            <span class="mdc-tab-indicator mdc-tab-indicator--active">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php echo form_open('', ['id' => 'op-data-form'], ['id' => $op['id']]); ?>
                        <table class="mdc-data-table__table">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Data</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Current value</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Override</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col">Edited</th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">

                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">ID</td>
                                    <td class="mdc-data-table__cell" colspan="3"><?php echo html_escape($op['id']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Date</td>
                                    <td class="mdc-data-table__cell" colspan="3"><?php echo html_escape($op['date']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Tag</td>
                                    <td class="mdc-data-table__cell" colspan="3"><?php echo html_escape($op['tag']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Mission</td>
                                    <td class="mdc-data-table__cell" colspan="3"><?php echo html_escape($op['mission_name']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Filename</td>
                                    <td class="mdc-data-table__cell" colspan="3">
                                        <span class="mdc-typography--caption">
                                            <a target="_blank" title="OCAP" href="<?php echo OCAP_URL_PREFIX . rawurlencode($op['filename']); ?>"><?php echo html_escape($op['filename']); ?></a>
                                        </span>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Map</td>
                                    <td class="mdc-data-table__cell" colspan="3"><?php echo html_escape($op['world_name']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Duration</td>
                                    <td class="mdc-data-table__cell" colspan="3"><?php echo $duration_min; ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Players</td>
                                    <td class="mdc-data-table__cell" colspan="3">
                                        <p>
                                            <?php
                                            $pps = [];
                                            foreach ($op_sides as $s => $pc) {
                                                $pps[] = '<span class="side__' . html_escape(strtolower($s)) . '">' . $sides[$s] . '</span> ' . $pc;
                                            }
                                            ?>
                                            <?php echo $op['players']; ?>
                                            <small>(<?php echo implode(' + ', $pps); ?>)</small>
                                        </p>
                                    </td>
                                </tr>

                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Event</td>
                                    <td class="mdc-data-table__cell"><?php echo $event_types[$op['event']]; ?></td>
                                    <td class="mdc-data-table__cell">#todo</td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                        <div class="mdc-form-field filters_checkbox">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'event-' . $op['id']; ?>" name="o_event" value="<?php echo $op['id']; ?>" <?php echo $extra_attr; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Winner</td>
                                    <td class="mdc-data-table__cell"><?php print_end_winners($op['end_winner']); ?></td>
                                    <td class="mdc-data-table__cell">#todo</td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                        <div class="mdc-form-field filters_checkbox">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'end_winner-' . $op['id']; ?>" name="oad_end_winner" value="<?php echo $op['id']; ?>" <?php echo $extra_attr; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">End message</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['end_message']); ?></td>
                                    <td class="mdc-data-table__cell">#todo</td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                        <div class="mdc-form-field filters_checkbox">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'end_message-' . $op['id']; ?>" name="oad_end_message" value="<?php echo $op['id']; ?>" <?php echo $extra_attr; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Author</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['mission_author']); ?></td>
                                    <td class="mdc-data-table__cell">#todo</td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                        <div class="mdc-form-field filters_checkbox">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'mission_author-' . $op['id']; ?>" name="oad_mission_author" value="<?php echo $op['id']; ?>" <?php echo $extra_attr; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">
                                        <p>
                                            Start time (UTC)
                                            <br>
                                            &nbsp; &rdca; Europe/London
                                            <br>
                                            &nbsp; &rdca; America/New_York
                                        </p>
                                    </td>
                                    <td class="mdc-data-table__cell">
                                        <p>
                                            <?php echo html_escape($op['start_time']); ?>
                                            <br>
                                            &nbsp; &rdca; <?php echo (new \DateTime($op['start_time'], new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('Europe/London'))->format('Y-m-d H:i:s'); ?>
                                            <br>
                                            &nbsp; &rdca; <?php echo (new \DateTime($op['start_time'], new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('America/New_York'))->format('Y-m-d H:i:s'); ?>
                                        </p>
                                    </td>
                                    <td class="mdc-data-table__cell">#todo</td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                        <div class="mdc-form-field filters_checkbox">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'start_time-' . $op['id']; ?>" name="oad_start_time" value="<?php echo $op['id']; ?>" <?php echo $extra_attr; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php foreach ($op_sides as $s => $pc) :
                                    $cmd_icon = $warn_icon;
                                    if (isset($hq_verified[$s])) {
                                        $cmd_icon = $fixed_icon;
                                    } elseif (isset($hq_unambiguous[$s]) && count($hq_unambiguous[$s]) > 1) {
                                        $cmd_icon = $flaky_icon;
                                    }
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">
                                            <?php
                                            echo '<span class="side__' . html_escape(strtolower($s)) . '">' . $sides[$s] . '</span> CMD ' . $cmd_icon;
                                            ?>
                                        </td>
                                        <td class="mdc-data-table__cell">
                                            <?php
                                            if (isset($hq_resolved[$s])) {
                                                print_hq_entity_info($hq_resolved[$s]);
                                            }
                                            ?>
                                        </td>
                                        <td class="mdc-data-table__cell" colspan="2">
                                            <p>
                                                <small>
                                                    <?php
                                                    if (isset($hq_verified[$s])) {
                                                        echo '<u>Verified</u> #todo<br>';
                                                        print_hq_entity_info($hq_verified[$s]);
                                                    }
                                                    if (isset($hq_unambiguous[$s])) {
                                                        echo '<br><u>Prospect</u><br>';
                                                        print_hq_entity_info($hq_unambiguous[$s]);
                                                    }
                                                    if (isset($hq_ambiguous[$s])) {
                                                        echo '<br><u>Prospects</u><br>';
                                                        foreach ($hq_ambiguous[$s] as $c) {
                                                            print_hq_entity_info($c);
                                                        }
                                                    }
                                                    ?>
                                                </small>
                                            </p>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Updated</td>
                                    <td class="mdc-data-table__cell" colspan="3">
                                        <p>
                                            <?php echo gmdate('Y-m-d H:i:s', $op['updated']); ?>
                                            <br>
                                            <?php echo timespan($op['updated'], '', 2); ?> ago
                                        </p>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td colspan="4" class="mdc-data-table__cell">
                                        <div class="mdc-form-field filters_checkbox">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'verified-' . $op['id']; ?>" name="oad_verified" value="<?php echo html_escape($op_ad['verified']); ?>" <?php echo $extra_attr; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                            <label for="<?php echo 'op-' . $op['id']; ?>">All data verified</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td colspan="4" class="mdc-data-table__cell">
                                        <button type="submit" name="action" value="parse" class="mdc-button mdc-button--raised mdc-button--leading">
                                            <span class="mdc-button__ripple"></span>
                                            <i class="material-icons mdc-button__icon" aria-hidden="true">save</i>
                                            <span class="mdc-button__label">Save</span>
                                        </button>
                                        <button type="reset" name="reset" value="0" class="mdc-button mdc-button--outlined">
                                            <span class="mdc-button__ripple"></span>
                                            <i class="material-icons mdc-button__icon" aria-hidden="true">cancel</i>
                                            <span class="mdc-button__label">Reset</span>
                                        </button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>


    </div>
</div>