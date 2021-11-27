<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
?>

<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">


        <?php if (count($errors) > 0) : ?>
            <div class="errors mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <h3>⚠️ Errors</h3>
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <?php if ($operation) :
            $op_parsed = ($op_in_db && $op && $op['event'] !== '') ? true : false;
        ?>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">
                        <?php echo form_open('', ['id' => 'manage-form'], ['id' => $operation['id']]); ?>
                        <table class="mdc-data-table__table">
                            <tbody class="mdc-data-table__content">
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">ID</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($operation['id']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Mission</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($operation['mission_name']); ?></td>
                                </tr>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Map</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($operation['world_name']); ?></td>
                                </tr>
                                <?php if ($op_parsed) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Winner</td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($op['end_winner']); ?></td>
                                    </tr>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">End message</td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($op['end_message']); ?></td>
                                    </tr>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Author</td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($op['mission_author']); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (isset($operation['tag'])) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Tag</td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($operation['tag']); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (isset($operation['can_import'])) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Can import</td>
                                        <td class="mdc-data-table__cell"><?php echo $operation['can_import'] ? 'Yes' : 'No'; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($op_parsed) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Event</td>
                                        <td class="mdc-data-table__cell"><?php echo $event_types[$op['event']]; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Date</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($operation['date']); ?></td>
                                </tr>
                                <?php if ($op_parsed) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">
                                            Start time (UTC)
                                            <br>
                                            &nbsp; &rdca; Europe/London
                                            <br>
                                            &nbsp; &rdca; America/New_York
                                        </td>
                                        <td class="mdc-data-table__cell">
                                            <?php echo html_escape($op['start_time']); ?>
                                            <br>
                                            &nbsp; &rdca; <?php echo (new \DateTime($op['start_time'], new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('Europe/London'))->format('Y-m-d H:i:s'); ?>
                                            <br>
                                            &nbsp; &rdca; <?php echo (new \DateTime($op['start_time'], new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('America/New_York'))->format('Y-m-d H:i:s'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Duration</td>
                                    <td class="mdc-data-table__cell"><?php echo floor(intval($operation['mission_duration']) / 60); ?>m</td>
                                </tr>
                                <?php if ($op_parsed) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Players</td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($op['players']); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="mdc-data-table__row">
                                    <td class="mdc-data-table__cell">Filename</td>
                                    <td class="mdc-data-table__cell">
                                        <span class="mdc-typography--caption">
                                            <a target="_blank" title="OCAP" href="<?php echo OCAP_URL_PREFIX . rawurlencode($operation['filename']); ?>"><?php echo html_escape($operation['filename']); ?></a>
                                        </span>
                                    </td>
                                </tr>
                                <?php if (defined('MANAGE_DATA_JSON_FILES') || $last_update !== 'none') : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">File date</td>
                                        <td class="mdc-data-table__cell">
                                            <?php echo html_escape($last_update); ?> (<?php echo html_escape($file_size); ?>)
                                            <?php if (defined('MANAGE_DATA_JSON_FILES')) : ?>
                                                <button type="submit" name="action" value="update" class="mdc-button mdc-button--leading">
                                                    <span class="mdc-button__ripple"></span>
                                                    <?php if ($last_update === 'none') : ?>
                                                        <i class="material-icons mdc-button__icon" aria-hidden="true">file_download</i>
                                                        <span class="mdc-button__label">Download</span>
                                                    <?php else : ?>
                                                        <i class="material-icons mdc-button__icon" aria-hidden="true">sync</i>
                                                        <span class="mdc-button__label">Update</span>
                                                    <?php endif; ?>
                                                </button>
                                                <?php if ($last_update !== 'none') : ?>
                                                    <button type="submit" name="action" value="del" class="mdc-button mdc-button--outlined">
                                                        <span class="mdc-button__ripple"></span>
                                                        <i class="material-icons mdc-button__icon" aria-hidden="true">delete_forever</i>
                                                        <span class="mdc-button__label">Del</span>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($op_in_db) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell">Updated</td>
                                        <td class="mdc-data-table__cell"><?php echo gmdate('Y-m-d H:i:s', $op['updated']); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($op_in_db === false) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td colspan="2" class="mdc-data-table__cell">
                                            <div class="mdc-form-field">
                                                <?php foreach ($event_types as $id => $name) :
                                                    $extra_class = '';
                                                    $extra_attr = '';

                                                    if (in_array($id, $valid_event_types)) {
                                                        if (!$should_ignore && count($valid_event_types) === 1) {
                                                            $extra_attr = ' checked';
                                                        }
                                                    } else {
                                                        $extra_class = ' radio--disabled';
                                                        $extra_attr = ' disabled';
                                                    }
                                                ?>
                                                    <div class="mdc-radio<?php echo $extra_class; ?>">
                                                        <input class="mdc-radio__native-control" type="radio" id="event-<?php echo $id ?>" name="event" value="<?php echo $id ?>" <?php echo $extra_attr; ?>>
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
                                        <td colspan="2" class="mdc-data-table__cell">
                                            <button type="submit" name="action" value="parse" class="mdc-button mdc-button--raised mdc-button--leading" <?php echo (defined('MANAGE_DATA_JSON_FILES') && $last_update === 'none') ? ' disabled' : ''; ?>>
                                                <span class="mdc-button__ripple"></span>
                                                <i class="material-icons mdc-button__icon" aria-hidden="true">publish</i>
                                                <span class="mdc-button__label">Parse operation data</span>
                                            </button>
                                            <button type="submit" name="action" value="ignore" class="mdc-button mdc-button--outlined">
                                                <span class="mdc-button__ripple"></span>
                                                <i class="material-icons mdc-button__icon" aria-hidden="true">not_interested</i>
                                                <span class="mdc-button__label">Ignore</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php elseif ($op_in_db === true) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td colspan="2" class="mdc-data-table__cell">
                                            <button type="submit" name="action" value="purge" class="mdc-button mdc-button--outlined">
                                                <span class="mdc-button__ripple"></span>
                                                <i class="material-icons mdc-button__icon" aria-hidden="true">delete</i>
                                                <span class="mdc-button__label">Purge</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>


    </div>
</div>