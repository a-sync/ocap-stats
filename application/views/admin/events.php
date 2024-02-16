<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
$sides = $this->config->item('sides');

$warn_icon = '<span class="material-icons">warning</span>';
$flaky_icon = '<span class="material-icons">flaky</span>';
$fixed_icon = '<span class="material-icons">check</span>';


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
            $duration_sec = floor(intval($op['mission_duration']) % 60);

            $verified = boolval(intval($op['verified']));
            $verified_attr = $verified ? ' disabled' : '';
            $verified_class = $verified ? ' mdc-text-field--disabled' : '';
        ?>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <div class="mdc-tab-bar">
                            <div class="mdc-tab-scroller">
                                <div class="mdc-tab-scroller__scroll-area">
                                    <div class="mdc-tab-scroller__scroll-content">
                                        <a href="<?php echo base_url('manage/' . $op['id']); ?>" class="mdc-tab" role="tab" aria-selected="false" tabindex="5">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Process data</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/verify'); ?>" class="mdc-tab" role="tab" aria-selected="false" tabindex="6">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Verify data</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/entities'); ?>" class="mdc-tab" role="tab" aria-selected="false" tabindex="7">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Entities</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/events'); ?>" class="mdc-tab mdc-tab--active" role="tab" aria-selected="true" tabindex="8">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Events</span>
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

                        <?php echo form_open('', ['id' => 'op-data-form', 'onreset' => 'on_reset_form()'], ['id' => $op['id']]); ?>
                        <table class="mdc-data-table__table">
                            <tbody class="mdc-data-table__content">
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">ID</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['id']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Date</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['date']); ?></td>
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
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Tag</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['tag']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Event</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($event_types[$op['event']]); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Mission</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['mission_name']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Filename</td>
                                    <td class="mdc-data-table__cell">
                                        <span class="mdc-typography--caption">
                                            <a target="_blank" title="OCAP" href="<?php echo OCAP_URL_PREFIX . rawurlencode($op['filename']); ?>"><?php echo html_escape($op['filename']); ?></a>
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
                                    <td class="mdc-data-table__cell"><span title="Capture delay">Duration</span></td>
                                    <td class="mdc-data-table__cell"><span title="<?php echo $op['capture_delay']; ?>"><?php echo $duration_min; ?>m <?php echo $duration_sec; ?>s</span></td>
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
                                    <td class="mdc-data-table__cell">Updated</td>
                                    <td class="mdc-data-table__cell">
                                        <p>
                                            <?php echo gmdate('Y-m-d H:i:s', $op['updated']); ?>
                                            <br>
                                            <?php echo strtolower(timespan($op['updated'], '', 2)); ?> ago
                                        </p>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td colspan="2" class="mdc-data-table__cell">
                                        <div class="mdc-form-field">
                                            <div class="mdc-touch-target-wrapper">
                                                <div class="mdc-checkbox mdc-checkbox--touch">
                                                    <input type="checkbox" class="mdc-checkbox__native-control" id="verified" name="verified" value="1" <?php echo $verified ? ' checked' : ''; ?>>
                                                    <div class="mdc-checkbox__background">
                                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                        </svg>
                                                        <div class="mdc-checkbox__mixedmark"></div>
                                                    </div>
                                                    <div class="mdc-checkbox__ripple"></div>
                                                </div>
                                            </div>
                                            <label for="verified">All data verified <span class="material-icons verified-icon"><?php echo $verified ? 'verified' : 'new_releases'; ?></span></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td colspan="2" class="mdc-data-table__cell">
                                        <button type="submit" name="action" value="update" class="mdc-button mdc-button--raised mdc-button--leading">
                                            <span class="mdc-button__ripple"></span>
                                            <span class="mdc-button__focus-ring"></span>
                                            <i class="material-icons mdc-button__icon" aria-hidden="true">save</i>
                                            <span class="mdc-button__label">Save</span>
                                        </button>
                                        <button type="reset" name="action" value="reset" class="mdc-button mdc-button--outlined">
                                            <span class="mdc-button__ripple"></span>
                                            <span class="mdc-button__focus-ring"></span>
                                            <i class="material-icons mdc-button__icon" aria-hidden="true">cancel</i>
                                            <span class="mdc-button__label">Reset</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php echo form_close(); ?>

                        <pre><?php
                            print_r([
                                'errors'=>$errors,
                                'op' => $op,
                                'op_sides' => $op_sides,
                                'op_player_entities' => $op_player_entities,
                                'cmd_resolved' => $cmd_resolved,
                                'cmd_verified' => $cmd_verified,
                                'cmd_unambiguous' => $cmd_unambiguous,
                                'cmd_ambiguous' => $cmd_ambiguous
                            ]);//dbg
                        ?></pre>
                    </div>
                </div>
            </div>
        <?php endif; ?>


    </div>
</div>
