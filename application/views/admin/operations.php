<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
?>
<button id="test1" onclick="test1()">test1</button>

<div class="mdc-select mdc-select--filled mdc-select--required op-event-select" data-mdc-auto-init="MDCSelect">
    <div class="mdc-select__anchor" role="button" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="op-event-label op-event-selected-text" aria-required="true">
        <span class="mdc-select__ripple"></span>
        <span id="op-event-label" class="mdc-floating-label">Event type</span>
        <span class="mdc-select__selected-text-container">
            <span id="op-event-selected-text" class="mdc-select__selected-text"></span>
        </span>
        <span class="mdc-select__dropdown-icon">
            <svg class="mdc-select__dropdown-icon-graphic" viewBox="7 10 10 5" focusable="false">
                <polygon class="mdc-select__dropdown-icon-inactive" stroke="none" fill-rule="evenodd" points="7 10 12 15 17 10">
                </polygon>
                <polygon class="mdc-select__dropdown-icon-active" stroke="none" fill-rule="evenodd" points="7 15 12 10 17 15">
                </polygon>
            </svg>
        </span>
        <span class="mdc-line-ripple"></span>
    </div>

    <div class="mdc-select__menu mdc-menu mdc-menu-surface mdc-menu-surface--fullwidth">
        <ul class="mdc-list" role="listbox" aria-label="Food picker listbox">
            <li class="mdc-list-item mdc-list-item--selected" aria-selected="true" data-value="" role="option">
                <span class="mdc-list-item__ripple"></span>
            </li>
            <li class="mdc-list-item" aria-selected="false" data-value="grains" role="option">
                <span class="mdc-list-item__ripple"></span>
                <span class="mdc-list-item__text">
                    NA
                </span>
            </li>
            <li class="mdc-list-item mdc-list-item--disabled" aria-selected="false" data-value="vegetables" aria-disabled="true" role="option">
                <span class="mdc-list-item__ripple"></span>
                <span class="mdc-list-item__text">
                    EU
                </span>
            </li>
            <li class="mdc-list-item" aria-selected="false" data-value="fruit" role="option">
                <span class="mdc-list-item__ripple"></span>
                <span class="mdc-list-item__text">
                    Titans
                </span>
            </li>
        </ul>
    </div>
</div>

<?php
//TODO: form[button-select] parse selected n* events as [SELECT](na,eu,etc)
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
                <div class="mdc-data-table mdc-data-table--sticky-header mdc-elevation--z2 list__table" data-mdc-auto-init="MDCDataTable">
                    <div class="mdc-data-table__table-container">
                        <table class="mdc-data-table__table" aria-label="OCAP recordings">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--checkbox" role="columnheader" scope="col">
                                        <div class="mdc-checkbox mdc-data-table__header-row-checkbox mdc-checkbox--selected">
                                            <input type="checkbox" class="mdc-checkbox__native-control" aria-label="Toggle all rows" />
                                            <div class="mdc-checkbox__background">
                                                <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                    <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59" />
                                                </svg>
                                                <div class="mdc-checkbox__mixedmark"></div>
                                            </div>
                                            <div class="mdc-checkbox__ripple"></div>
                                        </div>
                                    </th>
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
                                    $duration_sec = floor(intval($op['mission_duration']) % 60);

                                    $op_in_db = isset($ops_in_db[$op['id']]);
                                    $append_class = '';
                                    $append_chkbx_class = $op_in_db ? ' mdc-checkbox--disabled' : '';
                                    $append_chkbx_input_attr = $op_in_db ? ' disabled' : '';
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
                                    <tr class="mdc-data-table__row<?php echo $append_class; ?>" id="id-<?php echo $op['id']; ?>" data-row-id="op<?php echo $op['id']; ?>">
                                        <td class="mdc-data-table__cell mdc-data-table__cell--checkbox">
                                            <div class="mdc-checkbox mdc-data-table__row-checkbox<?php echo $append_chkbx_class; ?>">
                                                <input type="checkbox" class="mdc-checkbox__native-control" aria-labelledby="op<?php echo $op['id']; ?>"<?php echo $append_chkbx_input_attr; ?> />
                                                <div class="mdc-checkbox__background">
                                                    <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                        <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59" />
                                                    </svg>
                                                    <div class="mdc-checkbox__mixedmark"></div>
                                                </div>
                                                <div class="mdc-checkbox__ripple"></div>
                                            </div>
                                        </td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric mdc-typography--caption" id="op<?php echo $op['id']; ?>">
                                            <a href="<?php echo base_url('manage/' . $op['id']); ?>">
                                                <?php echo $op['id']; ?>
                                            </a>
                                        </td>
                                        <td class="mdc-data-table__cell"><span <?php echo $start_time_title; ?>><?php echo html_escape($op['date']); ?></span></td>
                                        <td class="mdc-data-table__cell cell__title">
                                            <?php echo html_escape($op['mission_name']); ?> (<span class="mdc-typography--subtitle2"><?php echo html_escape($op['world_name']); ?></span>)
                                            <br>
                                            <span class="mdc-typography--caption">
                                                <a title="OCAP" target="_blank" href="<?php echo OCAP_URL_PREFIX . rawurlencode($op['filename']); ?>"><?php echo html_escape($op['filename']); ?></a>
                                            </span>
                                        </td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $duration_min; ?>m <?php echo $duration_sec; ?>s</td>
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

                                            if ($vet_count > 0) {
                                                $hidden['event'] = $op_valid_event_types[0];
                                            }
                                        ?>
                                            <td class="mdc-data-table__cell" colspan="2">
                                                <?php echo form_open(base_url('manage/' . $op['id']), '', $hidden);
                                                if ($vet_count > 0 && !defined('MANAGE_DATA_JSON_FILES')) : ?>
                                                    <button type="submit" name="action" value="parse" class="mdc-button mdc-button--raised mdc-button--leading">
                                                        <span class="mdc-button__ripple"></span>
                                                        <i class="material-icons mdc-button__icon" aria-hidden="true">publish</i>
                                                        <span class="mdc-button__label">Parse as <?php echo $event_types[$hidden['event']]; ?></span>
                                                    </button>
                                                <?php elseif ($vet_count > 0) : ?>
                                                    <a href="<?php echo base_url('manage/' . $op['id']); ?>" class="mdc-button mdc-button--raised mdc-button--leading">
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

                    <div class="mdc-data-table__progress-indicator">
                        <div class="mdc-data-table__scrim"></div>
                        <div class="mdc-linear-progress mdc-linear-progress--indeterminate mdc-data-table__linear-progress" role="progressbar" aria-label="Data is being processed...">
                            <div class="mdc-linear-progress__buffer">
                                <div class="mdc-linear-progress__buffer-bar"></div>
                                <div class="mdc-linear-progress__buffer-dots"></div>
                            </div>
                            <div class="mdc-linear-progress__bar mdc-linear-progress__primary-bar">
                                <span class="mdc-linear-progress__bar-inner"></span>
                            </div>
                            <div class="mdc-linear-progress__bar mdc-linear-progress__secondary-bar">
                                <span class="mdc-linear-progress__bar-inner"></span>
                            </div>
                        </div>
                    </div>

                    <?php /*
                    <div class="mdc-data-table__pagination">
                        <div class="mdc-data-table__pagination-trailing">
                            <div class="mdc-data-table__pagination-rows-per-page">
                                <div class="mdc-data-table__pagination-rows-per-page-label">
                                    Rows per page
                                </div>

                                <div class="mdc-select mdc-select--outlined mdc-select--no-label mdc-data-table__pagination-rows-per-page-select">
                                    <div class="mdc-select__anchor" role="button" aria-haspopup="listbox" aria-labelledby="demo-pagination-select" tabindex="0">
                                        <span class="mdc-select__selected-text-container">
                                            <span id="demo-pagination-select" class="mdc-select__selected-text">10</span>
                                        </span>
                                        <span class="mdc-select__dropdown-icon">
                                            <svg class="mdc-select__dropdown-icon-graphic" viewBox="7 10 10 5">
                                                <polygon class="mdc-select__dropdown-icon-inactive" stroke="none" fill-rule="evenodd" points="7 10 12 15 17 10">
                                                </polygon>
                                                <polygon class="mdc-select__dropdown-icon-active" stroke="none" fill-rule="evenodd" points="7 15 12 10 17 15">
                                                </polygon>
                                            </svg>
                                        </span>
                                        <span class="mdc-notched-outline mdc-notched-outline--notched">
                                            <span class="mdc-notched-outline__leading"></span>
                                            <span class="mdc-notched-outline__trailing"></span>
                                        </span>
                                    </div>

                                    <div class="mdc-select__menu mdc-menu mdc-menu-surface mdc-menu-surface--fullwidth" role="listbox">
                                        <ul class="mdc-list">
                                            <li class="mdc-list-item mdc-list-item--selected" aria-selected="true" role="option" data-value="10">
                                                <span class="mdc-list-item__text">10</span>
                                            </li>
                                            <li class="mdc-list-item" role="option" data-value="25">
                                                <span class="mdc-list-item__text">25</span>
                                            </li>
                                            <li class="mdc-list-item" role="option" data-value="100">
                                                <span class="mdc-list-item__text">100</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="mdc-data-table__pagination-navigation">
                                <div class="mdc-data-table__pagination-total">
                                    1‑10 of <?php echo count($operations); ?>
                                </div>
                                <button class="mdc-icon-button material-icons mdc-data-table__pagination-button" data-first-page="true" disabled>
                                    <div class="mdc-button__icon">first_page</div>
                                </button>
                                <button class="mdc-icon-button material-icons mdc-data-table__pagination-button" data-prev-page="true" disabled>
                                    <div class="mdc-button__icon">chevron_left</div>
                                </button>
                                <button class="mdc-icon-button material-icons mdc-data-table__pagination-button" data-next-page="true">
                                    <div class="mdc-button__icon">chevron_right</div>
                                </button>
                                <button class="mdc-icon-button material-icons mdc-data-table__pagination-button" data-last-page="true">
                                    <div class="mdc-button__icon">last_page</div>
                                </button>
                            </div>
                        </div>
                    </div>
                    */ ?>
                <?php endif; ?>
                </div>
            </div>

            <script>
                const update_operations = document.getElementById('update_operations');
                update_operations.onsubmit = () => {
                    const dataTable = document.querySelector('.mdc-data-table').MDCDataTable;
                    dataTable.showProgress();
                };

                let progress = false;

                function test1() {
                    const dataTable = document.querySelector('.mdc-data-table').MDCDataTable;

                    if (progress) {
                        dataTable.hideProgress();
                    } else {
                        dataTable.showProgress();
                    }
                    progress = !progress;

                    const selected = dataTable.getSelectedRowIds();
                    console.log('dataTable:selected', selected);
                }
            </script>