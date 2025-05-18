<?php defined('BASEPATH') or exit('No direct script access allowed');

?>
<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 margin--center text--center">
            <?php if (isset($title) && $title !== '') {
                echo '<h1 class="mdc-typography--headline4">' . html_escape($title) . '</h1>';
            }

            foreach ($tables as $title => $arr) :
                $head = '';
                $body = '';

                if (count($arr) > 0) {
                    $head .= '<div class="array-table"><h2>' . $title . '</h2><div class="mdc-data-table mdc-elevation--z2"><div class="mdc-data-table__table-container"><table class="mdc-data-table__table sortable"><thead><tr class="mdc-data-table__header-row"><th class="mdc-data-table__header-cell mdc-data-table__header-cell--numeric" role="columnheader" scope="col" aria-sort="none" data-column-id="item_nr">#</th>';

                    $keys = array_keys($arr[0]);
                    $numeric = [];
                    foreach ($keys as $i => $k) {
                        $extra_class = '';
                        if (is_numeric($arr[0][$k])) {
                            $numeric[$k] = true;
                            $extra_class = ' mdc-data-table__header-cell--numeric';
                        }
                        $head .= '<th class="mdc-data-table__header-cell' . $extra_class . '" role="columnheader" scope="col" aria-sort="none" data-column-id="' . html_escape($k) . '">' . html_escape($k) . '</th>';
                    }

                    $body .= '<tbody class="mdc-data-table__content">';
                    foreach ($arr as $i => $a) {
                        $body .= '<tr class="mdc-data-table__row"><td class="mdc-data-table__cell mdc-data-table__cell--numeric">' . ($i + 1) . '</td>';
                        foreach ($a as $k => $val) {
                            $extra_class = isset($numeric[$k]) ? ' mdc-data-table__cell--numeric' : '';
                            $body .= '<td class="mdc-data-table__cell' . $extra_class . '">' . html_escape($val) . '</td>';
                        }
                        $body .= '</tr>';
                    }

                    $head .= '</tr></thead>';
                    $body .= '</tbody></table></div></div></div>';
                }

                echo $head . $body;
            endforeach; ?>
        </div>
    </div>
</div>