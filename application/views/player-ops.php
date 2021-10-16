<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (count($items) === 0) :
    echo '<div class="mdc-typography--body1 list__no_items">No ops found...</div>';
else :
    $unique_count = count(array_unique(array_column($items, 'operation_id')));
    echo '<div class="mdc-typography--caption list__total">' . $unique_count . ' ops</div>';
    // echo '<pre>'.print_r($items, true).'</pre>';

    $event_types = $this->config->item('event_types');
    $sides = $this->config->item('sides');
?>
    <div class="mdc-layout-grid">
        <div class="mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
                <div class="mdc-data-table mdc-elevation--z2">
                    <div class="mdc-data-table__table-container">

                        <div class="mdc-tab-bar">
                            <div class="mdc-tab-scroller">
                                <div class="mdc-tab-scroller__scroll-area">
                                    <div class="mdc-tab-scroller__scroll-content">
                                        <a class="mdc-tab mdc-tab--active" role="tab" aria-selected="true" tabindex="0">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Ops</span>
                                            </span>
                                            <span class="mdc-tab-indicator mdc-tab-indicator--active">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <!--
                                        <a class="mdc-tab" role="tab" aria-selected="false" tabindex="1">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Roles</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a class="mdc-tab" role="tab" aria-selected="false" tabindex="1">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Weapons</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a class="mdc-tab" role="tab" aria-selected="false" tabindex="1">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Attackers</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a class="mdc-tab" role="tab" aria-selected="false" tabindex="1">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Victims</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        <a class="mdc-tab" role="tab" aria-selected="false" tabindex="1">
                                            <span class="mdc-tab__content">
                                                <span class="mdc-tab__text-label">Rivals</span>
                                            </span>
                                            <span class="mdc-tab-indicator">
                                                <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                            </span>
                                            <span class="mdc-tab__ripple"></span>
                                        </a>
                                        -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="op_id">ID</th>
                                    <th class="mdc-data-table__header-cell cell__title" role="columnheader" scope="col" aria-sort="none" data-column-id="op_info" title="Event @ Start time (Duration)">
                                        Mission <sup class="mdc-typography--caption">(Map) Winner</sup>
                                    </th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="group">Group</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="name" title="Name">Role</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="shots" title="Shots">S</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="hits" title="Hits">H</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="kills" title="Kills">K</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="deaths" title="Deaths">D</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fhits" title="Friendly fire">FF</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="fkills" title="Teamkills">Tk</th>
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="vkills" title="Destroyed assets">DA</th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php
                                $prev_op_id = 0;
                                $merged_indexes = [];
                                foreach ($items as $index => $i) :
                                    if (in_array($index, $merged_indexes)) {
                                        continue;
                                    }
                                    $duration_min = floor(intval($i['mission_duration']) / 60);

                                    $next_index = $index + 1;
                                    $next_i = isset($items[$next_index]) ? $items[$next_index] : false;
                                    while ($next_i !== false && $i['operation_id'] === $next_i['operation_id']) {
                                        if (
                                            $i['name'] === $next_i['name']
                                            && $i['side'] === $next_i['side']
                                            && $i['role'] === $next_i['role']
                                        ) {
                                            $i['shots'] += $next_i['shots'];
                                            $i['hits'] += $next_i['hits'];
                                            $i['fhits'] += $next_i['fhits'];
                                            $i['kills'] += $next_i['kills'];
                                            $i['fkills'] += $next_i['fkills'];
                                            $i['vkills'] += $next_i['vkills'];
                                            $i['deaths'] += $next_i['deaths'];

                                            $merged_indexes[] = $next_index;
                                        }

                                        $next_index++;
                                        $next_i = isset($items[$next_index]) ? $items[$next_index] : false;
                                    }

                                    if ($i['role'] === '') {
                                        $role = '';
                                        $group = $i['group_name'];
                                    } else {
                                        $role_group_arr = explode('@', $i['role']);
                                        $role = $role_group_arr[0];
                                        $group = isset($role_group_arr[1]) ? $role_group_arr[1] : '';
                                        $group = str_replace('Reconnaissance', 'Recon', $group);
                                    }

                                    $new_op_row = ($prev_op_id !== $i['operation_id']); 
                                ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                                <a href="<?php echo base_url('op/') . $i['operation_id']; ?>"><?php echo $i['operation_id']; ?></a>
                                            </td>
                                            <td class="mdc-data-table__cell">
                                                <span title="<?php echo $event_types[$i['event']]; ?> @ <?php echo $i['start_time']; ?> (<?php echo $duration_min ?>m)">
                                                <?php echo html_escape($i['mission_name']); ?>&nbsp;<sup class="mdc-typography--caption"><a target="_blank" title="AAR" href="<?php echo FNF_AAR_URL_PREFIX . urlencode($i['filename']); ?>">AAR</a>
                                                    (<?php echo html_escape($i['world_name']); ?>) <?php echo $sides[$i['end_winner']]; ?>
                                                    </sup>
                                                </span>
                                            </td>
                                        <td class="mdc-data-table__cell <?php echo 'side__'.html_escape(strtolower($i['side'])); ?>">
                                            <?php echo $sides[$i['side']]; ?> <?php echo html_escape($group); ?>
                                        </td>
                                        <td class="mdc-data-table__cell">
                                            <span title="<?php echo html_escape($i['name']); ?>"><?php echo html_escape($role); ?></span>
                                        </td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['shots']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php if($i['operation_id'] >= FIRST_OP_WITH_HIT_EVENTS) { echo $i['hits']; } ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['kills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['deaths']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php if($i['operation_id'] >= FIRST_OP_WITH_HIT_EVENTS) { echo $i['fhits']; } ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['fkills']; ?></td>
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric"><?php echo $i['vkills']; ?></td>
                                    </tr>
                                <?php
                                    $prev_op_id = $i['operation_id'];
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>