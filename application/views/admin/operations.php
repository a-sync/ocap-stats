<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
?>

<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6 flex--center update_field">
            <?php echo form_open(base_url('update'), ['id' => 'update_operations']); ?>
            <button type="submit" name="update_operations" value="1" class="mdc-button mdc-button--raised mdc-button--leading">
                <span class="mdc-button__ripple"></span>
                <i class="material-icons mdc-button__icon" aria-hidden="true">sync</i>
                <span class="mdc-button__label">Update operations.json</span>
            </button>
            <br>
            <i class="mdc-typography--caption operations_json_info">
                <?php echo count($operations); ?> entries (<?php echo convert_filesize($file_size); ?>)
                <br>
                <?php echo $last_update ? 'updated ' . strtolower(timespan($last_update, '', 2)) . ' ago' : 'not downloaded'; ?>
            </i>
            <?php echo form_close(); ?>
        </div>
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6 flex--center update_field">
            <?php echo form_open(base_url('clearcache'), ['id' => 'clear_cache']); ?>
            <button type="submit" name="clear_cache" value="1" class="mdc-button mdc-button--outlined">
                <span class="mdc-button__ripple"></span>
                <i class="material-icons mdc-button__icon" aria-hidden="true">auto_delete</i>
                <span class="mdc-button__label">Clear site cache</span>
            </button>
            <br>
            <i class="mdc-typography--caption operations_json_info">
                index <?php echo $last_cache_update ? 'cached ' . strtolower(timespan($last_cache_update, '', 2)) . ' ago' : 'not cached'; ?>
            </i>
            <?php echo form_close(); ?>
        </div>
        <?php
        if (count($operations) === 0) :
            echo '<div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 mdc-typography--body1 list__no_items">operations.json is empty...</div>';
        else :
            $op0 = $operations[0];
        ?>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <div class="mdc-data-table mdc-elevation--z2 list__table">
                    <div class="mdc-data-table__table-container">
                        <table class="mdc-data-table__table">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col">ID</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" title="Start time">Date</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Mission (Map)</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col">Duration</th>
                                    <?php if (isset($op0['tag'])) : ?>
                                        <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Tag</th>
                                    <?php endif; ?>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Event</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php foreach ($operations as $op) :
                                    $duration_min = floor(intval($op['mission_duration']) / 60);

                                    $op_in_db = isset($ops_in_db[$op['id']]);
                                    $append_class = '';
                                    $label = '';
                                    $start_time_title = '';
                                    if ($op_in_db) {
                                        if ($ops_in_db[$op['id']]['event'] === '') {
                                            $append_class .= ' ignored_operation';
                                            $label = '<i>ignored</i>';
                                        } else {
                                            $label = '<span>' . $event_types[$ops_in_db[$op['id']]['event']] . '</span>';
                                            $start_time_title = ' title="' . html_escape($ops_in_db[$op['id']]['start_time']) . '"';
                                        }
                                    }
                                ?>
                                    <tr class="mdc-data-table__row<?php echo $append_class; ?>" id="id-<?php echo $op['id']; ?>">
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric mdc-typography--caption">
                                            <a href="<?php echo base_url('manage/' . $op['id']); ?>">
                                                <?php echo $op['id']; ?>
                                            </a>
                                        </td>
                                        <td class="mdc-data-table__cell" <?php echo $start_time_title; ?>><?php echo html_escape($op['date']); ?></td>
                                        <td class="mdc-data-table__cell cell__title">
                                            <?php echo html_escape($op['mission_name']); ?> (<span class="mdc-typography--subtitle2"><?php echo html_escape($op['world_name']); ?></span>)
                                            <br>
                                            <span class="mdc-typography--caption">
                                                <a title="OCAP" target="_blank" href="<?php echo OCAP_URL_PREFIX . rawurlencode($op['filename']); ?>"><?php echo html_escape($op['filename']); ?></a>
                                            </span>
                                        </td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $duration_min; ?>m</td>
                                        <?php if (isset($op['tag'])) : ?>
                                            <td class="mdc-data-table__cell"><?php echo html_escape($op['tag']); ?></td>
                                        <?php endif; ?>
                                        <?php if ($op_in_db) : ?>
                                            <td class="mdc-data-table__cell">
                                                <?php echo $label; ?>
                                            </td>
                                            <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                                <span title="<?php echo gmdate('Y-m-d H:i:s', $ops_in_db[$op['id']]['updated']); ?>">
                                                    <?php echo strtolower(timespan($ops_in_db[$op['id']]['updated'], '', 1)); ?> ago
                                                </span>
                                            </td>
                                        <?php else :
                                            $vet_count = 0;
                                            $op_valid_event_types = get_valid_event_types($op);
                                            $op_should_be_ignored = should_ignore($op);
                                            if (!$op_should_be_ignored) {
                                                $vet_count = count($op_valid_event_types);
                                            }

                                            $hidden = [
                                                'id' => $op['id'],
                                                'redirect' => 'list'
                                            ];

                                            if ($vet_count === 1) {
                                                $hidden['event'] = $op_valid_event_types[0];
                                            }
                                        ?>
                                            <td class="mdc-data-table__cell" colspan="2">
                                                <?php echo form_open(base_url('manage/' . $op['id']), '', $hidden);
                                                if ($vet_count === 1 && !defined('MANAGE_DATA_JSON_FILES')) : ?>
                                                    <button type="submit" name="action" value="parse" class="mdc-button mdc-button--raised mdc-button--leading">
                                                        <span class="mdc-button__ripple"></span>
                                                        <i class="material-icons mdc-button__icon" aria-hidden="true">publish</i>
                                                        <span class="mdc-button__label">Parse as <?php echo $event_types[$hidden['event']]; ?></span>
                                                    </button>
                                                <?php elseif ($vet_count > 0) : ?>
                                                    <a href="<?php echo base_url('manage/' . $op['id']); ?>" class="mdc-button mdc-button--outlined">
                                                        <span class="mdc-button__ripple"></span>
                                                        <span class="mdc-button__label">Process</span>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="submit" name="action" value="ignore" class="mdc-button mdc-button--outlined">
                                                    <span class="mdc-button__ripple"></span>
                                                    <i class="material-icons mdc-button__icon" aria-hidden="true">not_interested</i>
                                                    <span class="mdc-button__label">Ignore</span>
                                                </button>
                                                <?php echo form_close(); ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>