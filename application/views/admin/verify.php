<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
$sides = $this->config->item('sides');

$warn_icon = '<span class="material-icons">warning</span>';
$flaky_icon = '<span class="material-icons">flaky</span>';
$fixed_icon = '<span class="material-icons">check</span>';

function print_cmd_entity_info($entity, $prospect = false)
{
    if ($prospect && $entity['role'] === '') {
        echo html_escape($entity['group_name']) . ' ';
    }
    echo '#' . $entity['entity_id'] . ' ' . html_escape($entity['entity_name']);
    if ($entity['role'] !== '') {
        echo ' (' . html_escape($entity['role']) . ')';
    }
}

$op_player_entities_grouped = [];
foreach ($op_player_entities as $e) {
    if (!isset($op_player_entities_grouped[$e['side']])) {
        $op_player_entities_grouped[$e['side']] = [];
    }

    $g = $e['group_name'] ? $e['group_name'] : $e['side'];
    if (isset($op_player_entities_grouped[$e['side']][$g])) {
        $op_player_entities_grouped[$e['side']][$g][] = $e;
    } else {
        $op_player_entities_grouped[$e['side']][$g] = [$e];
    }
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
                                        <a href="<?php echo base_url('manage/' . $op['id']); ?>" class="mdc-tab" role="tab" aria-selected="true" tabindex="5">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Process data</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a href="<?php echo base_url('manage/' . $op['id'] . '/verify'); ?>" class="mdc-tab mdc-tab--active" role="tab" aria-selected="false" tabindex="6">
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
                                            <?php
                                            if (substr($op['start_time'], -8) === '00:00:00') {
                                                echo $warn_icon;
                                            } elseif (substr($op['start_time'], -2) === '00') {
                                                echo $flaky_icon;
                                            }
                                            ?>
                                            <br>
                                            &nbsp; &rdca; Europe/London
                                            <br>
                                            &nbsp; &rdca; America/New_York
                                        </p>
                                    </td>
                                    <td class="mdc-data-table__cell">
                                        <p>
                                            <label class="mdc-text-field mdc-text-field--outlined<?php echo $verified_class; ?>">
                                                <span class="mdc-notched-outline">
                                                    <span class="mdc-notched-outline__leading"></span>
                                                    <span class="mdc-notched-outline__notch"></span>
                                                    <span class="mdc-notched-outline__trailing"></span>
                                                </span>
                                                <input type="text" class="mdc-text-field__input" name="start_time" minlength="19" maxlength="19" value="<?php echo html_escape($op['start_time']); ?>" pattern="^[0-2][0-9]{3}-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$" <?php echo $verified_attr; ?>>
                                            </label>
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
                                    <td class="mdc-data-table__cell">
                                        <div class="mdc-form-field">
                                            <?php $valid_event_types = get_valid_event_types($op);
                                            foreach ($event_types as $id => $name) :
                                                $extra_class = '';
                                                $extra_attr = '';

                                                if ($id === $op['event']) {
                                                    $extra_attr = ' checked';
                                                } elseif (!in_array($id, $valid_event_types)) {
                                                    $extra_class = ' radio--disabled';
                                                    $extra_attr = ' disabled';
                                                }
                                            ?>
                                                <div class="mdc-radio<?php echo $extra_class; ?>">
                                                    <input class="mdc-radio__native-control" type="radio" id="event-<?php echo $id ?>" name="event" value="<?php echo $id ?>" <?php echo $extra_attr . $verified_attr; ?>>
                                                    <div class="mdc-radio__background">
                                                        <div class="mdc-radio__outer-circle"></div>
                                                        <div class="mdc-radio__inner-circle"></div>
                                                    </div>
                                                    <div class="mdc-radio__ripple"></div>
                                                </div>
                                                <label for="event-<?php echo $id ?>"><?php echo html_escape($name); ?></label>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
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
                                    <td class="mdc-data-table__cell">
                                        Author
                                        <?php
                                        if ($op['mission_author'] === '') {
                                            echo $warn_icon;
                                        }
                                        ?>
                                    </td>
                                    <td class="mdc-data-table__cell">
                                        <label class="mdc-text-field mdc-text-field--outlined<?php echo $verified_class; ?>">
                                            <span class="mdc-notched-outline">
                                                <span class="mdc-notched-outline__leading"></span>
                                                <span class="mdc-notched-outline__notch"></span>
                                                <span class="mdc-notched-outline__trailing"></span>
                                            </span>
                                            <input type="text" class="mdc-text-field__input" name="mission_author" maxlength="255" value="<?php echo html_escape($op['mission_author']); ?>" <?php echo $verified_attr; ?>>
                                        </label>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Duration</td>
                                    <td class="mdc-data-table__cell"><?php echo $duration_min; ?>m <?php echo $duration_sec; ?>s</td>
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
                                    <td class="mdc-data-table__cell">
                                        Winner
                                        <?php
                                        if ($op['end_winner'] === '') {
                                            echo $warn_icon;
                                        }
                                        ?>
                                    </td>
                                    <td class="mdc-data-table__cell">
                                        <?php $winner_sides = explode('/', $op['end_winner']);
                                        foreach ($op_sides as $s => $pc) :
                                            if ($s !== '') :
                                                $extra_attr = '';
                                                if (in_array($s, $winner_sides)) {
                                                    $extra_attr = ' checked';
                                                }
                                        ?>
                                                <div class="mdc-form-field">
                                                    <div class="mdc-touch-target-wrapper">
                                                        <div class="mdc-checkbox mdc-checkbox--touch">
                                                            <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'end_winner-' . strtolower(html_escape($s)); ?>" name="end_winner[]" value="<?php echo html_escape($s); ?>" <?php echo $extra_attr . $verified_attr; ?>>
                                                            <div class="mdc-checkbox__background">
                                                                <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                                    <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                                                                </svg>
                                                                <div class="mdc-checkbox__mixedmark"></div>
                                                            </div>
                                                            <div class="mdc-checkbox__ripple"></div>
                                                        </div>
                                                    </div>
                                                    <label for="<?php echo 'end_winner-' . strtolower(html_escape($s)); ?>"><?php echo $pc > 0 ? $sides[$s] : '<i>' . $sides[$s] . '</i>'; ?></label>
                                                </div>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">
                                        End message
                                        <?php
                                        if ($op['end_message'] === '') {
                                            echo $warn_icon;
                                        }
                                        ?>
                                    </td>
                                    <td class="mdc-data-table__cell">
                                        <label class="mdc-text-field mdc-text-field--outlined<?php echo $verified_class; ?>">
                                            <span class="mdc-notched-outline">
                                                <span class="mdc-notched-outline__leading"></span>
                                                <span class="mdc-notched-outline__notch"></span>
                                                <span class="mdc-notched-outline__trailing"></span>
                                            </span>
                                            <input type="text" class="mdc-text-field__input" name="end_message" maxlength="255" value="<?php echo html_escape($op['end_message']); ?>" <?php echo $verified_attr; ?>>
                                        </label>
                                    </td>
                                </tr>
                                <?php
                                foreach ($op_sides as $s => $pc) :
                                    if ($pc > 0) :
                                        $cmd_icon = $warn_icon;
                                        if (isset($cmd_verified[$s])) {
                                            $cmd_icon = $fixed_icon;
                                        } elseif (isset($cmd_unambiguous[$s])) {
                                            $cmd_icon = $flaky_icon;
                                        }
                                ?>
                                        <tr class="mdc-data-table__row">
                                            <td class="mdc-data-table__cell">
                                                <?php
                                                echo '<span class="side__' . html_escape(strtolower($s)) . '">' . $sides[$s] . '</span> commander ' . $cmd_icon;
                                                ?>
                                            </td>
                                            <td class="mdc-data-table__cell">
                                                <p>
                                                <div class="mdc-form-field ss-container">
                                                    <select id="cmd-<?php echo html_escape(strtolower($s)); ?>" name="cmd[<?php echo html_escape($s); ?>]" <?php echo $verified_attr; ?>>
                                                        <option value="-1">-- no commander --</option>
                                                        <?php
                                                        $curr = isset($cmd_resolved[$s]) ? $cmd_resolved[$s]['entity_id'] : null;
                                                        $printed = [];
                                                        if (isset($cmd_unambiguous[$s])) {
                                                            echo '<optgroup label="Prospect"><option value="' . $cmd_unambiguous[$s]['entity_id'] . '"' . ($curr === $cmd_unambiguous[$s]['entity_id'] ? ' selected' : '') . '>';
                                                            print_cmd_entity_info($cmd_unambiguous[$s], true);
                                                            echo '</option></optgroup>';
                                                            $printed[] = $cmd_unambiguous[$s]['entity_id'];
                                                        }
                                                        if (isset($cmd_ambiguous[$s])) {
                                                            echo '<optgroup label="Prospects">';
                                                            foreach ($cmd_ambiguous[$s] as $c) {
                                                                if (!in_array($c['entity_id'], $printed)) {
                                                                    echo '<option value="' . $c['entity_id'] . '"' . ($curr === $c['entity_id'] ? ' selected' : '') . '>';
                                                                    print_cmd_entity_info($c, true);
                                                                    echo '</option>';
                                                                    $printed[] = $c['entity_id'];
                                                                }
                                                            }
                                                            echo '</optgroup>';
                                                        }
                                                        foreach ($op_player_entities_grouped[$s] as $g => $ents) {
                                                            echo '<optgroup label="' . html_escape($g) . '">';
                                                            foreach ($ents as $c) {
                                                                $extra_attr = '';
                                                                if (intval($c['invalid']) === 1) {
                                                                    $extra_attr = ' disabled';
                                                                } elseif ($c['entity_id'] === $curr) {
                                                                    $extra_attr = ' selected';
                                                                }
                                                                echo '<option value="' . $c['entity_id'] . '"' . $extra_attr . '>';
                                                                print_cmd_entity_info($c);
                                                                echo '</option>';
                                                            }
                                                            echo '</optgroup>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                </p>
                                            </td>
                                        </tr>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Updated</td>
                                    <td class="mdc-data-table__cell">
                                        <p>
                                            <?php echo gmdate('Y-m-d H:i:s', $op['updated']); ?>
                                            <br>
                                            <?php echo timespan($op['updated'], '', 2); ?> ago
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
                                            <i class="material-icons mdc-button__icon" aria-hidden="true">save</i>
                                            <span class="mdc-button__label">Save</span>
                                        </button>
                                        <button type="reset" name="action" value="reset" class="mdc-button mdc-button--outlined">
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

<script src="<?php echo base_url('public/slimselect.min.js'); ?>"></script>
<script>
    <?php
    foreach ($op_sides as $s => $pc) :
        if ($pc > 0) :
    ?>
            new SlimSelect({
                select: '#cmd-<?php echo html_escape(strtolower($s)); ?>',
                addToBody: true
            });
    <?php
        endif;
    endforeach;
    ?>

    function on_reset_form() {
        // make sure the SlimSelect instances reflect the current state
        setTimeout(() => {
            const ss_arr = document.querySelectorAll('.ss-container > select');
            const event = new Event('change');
            for (let i = 0; i < ss_arr.length; i++) {
                ss_arr[i].dispatchEvent(event);
            }
        }, 20);
    }
</script>