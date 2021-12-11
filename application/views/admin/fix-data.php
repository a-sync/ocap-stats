<?php defined('BASEPATH') or exit('No direct script access allowed');

if($tab === 'unverified') {
    $items_type = ' unverified ops';
} else { // missing
    $items_type = ' ops missing data';
}

if (count($items) === 0) {
    echo '<div class="mdc-typography--body1 list__no_items">No' . $items_type . '...</div>';
} else {
    echo '<div class="mdc-typography--caption list__total">' . count($items) . $items_type . '</div>';
}

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

        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
            <div class="mdc-data-table mdc-elevation--z2">
                <div class="mdc-data-table__table-container">
                    <div class="mdc-tab-bar">
                        <div class="mdc-tab-scroller">
                            <div class="mdc-tab-scroller__scroll-area">
                                <div class="mdc-tab-scroller__scroll-content">
                                    <a href="<?php echo base_url('fix-data'); ?>" class="mdc-tab<?php if ($tab === 'missing') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="true" tabindex="5">
                                        <span class="mdc-tab__content">
                                            <span class="mdc-tab__text-label">Missing data</span>
                                        </span>
                                        <span class="mdc-tab-indicator<?php if ($tab === 'missing') echo ' mdc-tab-indicator--active';  ?>">
                                            <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                        </span>
                                        <span class="mdc-tab__ripple"></span>
                                    </a>
                                    <a href="<?php echo base_url('fix-data/unverified'); ?>" class="mdc-tab<?php if ($tab === 'unverified') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="6">
                                        <span class="mdc-tab__content">
                                            <span class="mdc-tab__text-label">Unverified</span>
                                        </span>
                                        <span class="mdc-tab-indicator<?php if ($tab === 'unverified') echo ' mdc-tab-indicator--active';  ?>">
                                            <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                                        </span>
                                        <span class="mdc-tab__ripple"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (count($items) > 0) : ?>
                        <table class="mdc-data-table__table sortable">
                            <thead>
                                <tr class="mdc-data-table__header-row">
                                    <th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="descending" data-column-id="id">ID</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="date">Date</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="event" title="Tag">Event</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="mission">Mission</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="commanders">Commanders</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="winner" title="End message">Winner</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="author">Author</th>
                                    <th class="mdc-data-table__header-cell" role="columnheader" scope="col" aria-sort="none" data-column-id="start_time">Start time</th>
                                </tr>
                            </thead>
                            <tbody class="mdc-data-table__content">
                                <?php foreach ($items as $i) : ?>
                                    <tr class="mdc-data-table__row">
                                        <td class="mdc-data-table__cell mdc-data-table__cell--numeric">
                                            <a href="<?php echo base_url('fix-data/') . $i['id']; ?>" title="<?php echo html_escape($i['filename']); ?>"><?php echo $i['id']; ?></a>
                                        </td>
                                        <td class="mdc-data-table__cell"><?php echo html_escape($i['date']); ?></td>
                                        <td class="mdc-data-table__cell"><span title="<?php echo html_escape($i['tag']); ?>"><?php echo $event_types[$i['event']]; ?></span></td>
                                        <td class="mdc-data-table__cell cell__title">
                                            <?php echo html_escape($i['mission_name']); ?>&nbsp;<sup class="mdc-typography--caption"><a target="_blank" href="<?php echo OCAP_URL_PREFIX . rawurlencode($i['filename']); ?>"><img src="<?php echo base_url('public/ocap_logo.png'); ?>" alt="OCAP" class="ocap-link"></a>
                                            </sup>
                                        </td>
                                        <td class="mdc-data-table__cell">
                                            <?php
                                            $ops_hq_entities = [];
                                            $commanders_icon = $warn_icon;
                                            if (isset($hq_verified[$i['id']])) {
                                                $ops_hq_entities = $hq_verified[$i['id']];
                                                $commanders_icon = $fixed_icon;
                                            } elseif (isset($hq_unambiguous[$i['id']])) {
                                                $ops_hq_entities = $hq_unambiguous[$i['id']];
                                                if (count($hq_unambiguous[$i['id']]) > 1) {
                                                    $commanders_icon = $flaky_icon;
                                                }
                                            }

                                            $commanders_list = [];
                                            foreach ($ops_hq_entities as $side => $entity) {
                                                $commanders_list[] = '<span class="side__' . html_escape(strtolower($side)) . '">' . $entity['entity_name'] . '</span>';
                                            }

                                            echo implode(', ', $commanders_list) . ' ' . $commanders_icon;
                                            ?>
                                        </td>
                                        <td class="mdc-data-table__cell">
                                            <?php
                                            $end_message_icon = '';
                                            if (is_null($i['ad_end_message'])) {
                                                if ($i['end_message'] === '') {
                                                    $end_message_icon = ' ⚠️';
                                                }
                                            } else {
                                                $end_message_icon = ' ✔️';
                                            }
                                            ?>
                                            <?php print_end_winners($i['end_winner'], $i['end_message'].$end_message_icon); ?>
                                            <?php if (is_null($i['ad_end_winner'])) {
                                                if ($i['end_winner'] === '') {
                                                    echo $warn_icon;
                                                }
                                            } else {
                                                echo $fixed_icon;
                                            } ?>
                                        </td>
                                        <td class="mdc-data-table__cell">
                                            <?php echo html_escape($i['mission_author']); ?>
                                            <?php if (is_null($i['ad_mission_author'])) {
                                                if ($i['mission_author'] === '') {
                                                    echo $warn_icon;
                                                }
                                            } else {
                                                echo $fixed_icon;
                                            } ?>
                                        </td>
                                        <td class="mdc-data-table__cell">
                                            <?php echo $i['start_time']; ?>
                                            <?php if (is_null($i['ad_start_time'])) {
                                                if (substr($i['start_time'], -8) === '00:00:00') {
                                                    echo $warn_icon;
                                                } elseif (substr($i['start_time'], -2) === '00') {
                                                    echo $flaky_icon;
                                                }
                                            } else {
                                                echo $fixed_icon;
                                            } ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>


    </div>
</div>