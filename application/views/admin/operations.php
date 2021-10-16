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
                <?php echo count($operations); ?> entries (<?php echo $file_size; ?>)  
                <br>
                <?php echo $last_update ? 'updated '.strtolower(timespan($last_update, time())).' ago' : 'not downloaded'; ?>
            </i>
            <?php echo form_close(); ?>
        </div>
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6 flex--center update_field">
            <?php echo form_open(base_url('clear-cache'), ['id' => 'clear_cache']);?>
            <button type="submit" name="clear_cache" value="1" class="mdc-button mdc-button--outlined">
                <span class="mdc-button__ripple"></span>
                <i class="material-icons mdc-button__icon" aria-hidden="true">auto_delete</i>
                <span class="mdc-button__label">Clear site cache</span>
            </button>
            <br>
            <i class="mdc-typography--caption operations_json_info">
                index <?php echo $last_cache_update ? 'cached '.strtolower(timespan($last_cache_update, time())).' ago' : 'not cached'; ?>
            </i>
            <?php echo form_close(); ?>
        </div>

        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
            <div class="mdc-data-table mdc-elevation--z2 list__table">
                <div class="mdc-data-table__table-container">
                    <table class="mdc-data-table__table">
                        <thead>
                            <tr class="mdc-data-table__header-row">
                                <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col">ID</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col" title="Start time">Date</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Mission (map)</th>
                                <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col">Duration</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col">Tag</th>
                                <th class="mdc-data-table__header-cell" role="columnheader" scope="col" title="Updated">Event</th>
                            </tr>
                        </thead>
                        <tbody class="mdc-data-table__content">
                            <?php foreach ($operations as $op) :
                                $duration_min = floor(intval($op['mission_duration']) / 60);

                                $op_in_db = isset($op_db_ids[$op['id']]);
                                $append_class = '';
                                $label = '';
                                $start_time_title = '';
                                if ($op_in_db) {
                                    if ($op_db_ids[$op['id']]['event'] === '') {
                                        $append_class .= ' ignored_operation';
                                        $label = '<i title="'.html_escape($op_db_ids[$op['id']]['updated']).'">ignored</i>';
                                    } else {
                                        $label = '<span title="'.$op_db_ids[$op['id']]['updated'].'">'.$event_types[$op_db_ids[$op['id']]['event']].'</span>';
                                        $start_time_title = ' title="'.html_escape($op_db_ids[$op['id']]['start_time']).'"';
                                    }
                                }
                            ?>
                                <tr class="mdc-data-table__row<?php echo $append_class; ?>" id="id-<?php echo intval($op['id']); ?>">
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric mdc-typography--caption">
                                        <?php if ($op_in_db) : ?>
                                            <a href="<?php echo base_url('manage/' . intval($op['id'])); ?>">
                                                <?php echo intval($op['id']); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo intval($op['id']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="mdc-data-table__cell"<?php echo $start_time_title; ?>><?php echo html_escape($op['date']); ?></td>
                                    <td class="mdc-data-table__cell cell__title">
                                        <?php echo html_escape($op['mission_name']); ?> (<span class="mdc-typography--subtitle2"><?php echo html_escape($op['world_name']); ?></span>)
                                        <br>
                                        <span class="mdc-typography--caption">
                                            <a title="AAR" target="_blank" href="<?php echo FNF_AAR_URL_PREFIX . urlencode($op['filename']); ?>"><?php echo html_escape($op['filename']); ?></a>
                                        </span>
                                    </td>
                                    <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $duration_min; ?>m</td>
                                    <td class="mdc-data-table__cell"><?php echo html_escape($op['tag']); ?></td>
                                    <td class="mdc-data-table__cell">
                                        <?php if ($op_in_db) : ?>
                                            <?php echo $label; ?>
                                        <?php else : ?>
                                            <a href="<?php echo base_url('manage/' . intval($op['id'])); ?>" class="mdc-button mdc-button--outlined">
                                                <span class="mdc-button__ripple"></span>
                                                <span class="mdc-button__label">Process</span>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>