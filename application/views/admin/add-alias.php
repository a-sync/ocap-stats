<?php defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');
?>

<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">


        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center update_field">
            <?php echo form_open(base_url('clearcache'), ['id' => 'clear_cache'], ['redirect' => 'add-alias']); ?>
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

        <?php if (count($errors) > 0) : ?>
            <div class="errors mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <h3>⚠️ Errors</h3>
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
            <div class="mdc-data-table mdc-elevation--z2">
                <div class="mdc-data-table__table-container">
                    <?php echo form_open('', ['id' => 'add-alias-form']); ?>
                    <table class="mdc-data-table__table">
                        <tbody class="mdc-data-table__content">
                            <tr class="mdc-data-table__row">
                                <td class="mdc-data-table__cell">New player name</td>
                                <td class="mdc-data-table__cell">
                                    <label class="mdc-text-field mdc-text-field--outlined">
                                        <span class="mdc-notched-outline">
                                            <span class="mdc-notched-outline__leading"></span>
                                            <span class="mdc-notched-outline__notch"></span>
                                            <span class="mdc-notched-outline__trailing"></span>
                                        </span>
                                        <input type="text" class="mdc-text-field__input" name="new_player_name" maxlength="255">
                                    </label>
                                </td>
                            </tr>
                            <tr class="mdc-data-table__row">
                                <td colspan="2" class="mdc-data-table__cell">
                                    <button type="submit" name="add_new_player" value="1" class="mdc-button mdc-button--raised mdc-button--icon-trailing">
                                        <span class="mdc-button__ripple"></span>
                                        <i class="material-icons mdc-button__icon" aria-hidden="true">person_add</i>
                                        <span class="mdc-button__label">Add new player</span>
                                    </button>
                                </td>
                            </tr>
                            <tr class="mdc-data-table__row">
                                <td class="mdc-data-table__cell">Player</td>
                                <td class="mdc-data-table__cell">
                                    <div class="mdc-form-field ss-container">
                                        <select id="player-select" name="player_id">
                                            <option data-placeholder="true"></option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr class="mdc-data-table__row">
                                <td class="mdc-data-table__cell">Aliases</td>
                                <td class="mdc-data-table__cell">
                                    <div class="mdc-form-field ss-container">
                                        <select id="aliases-select" name="aliases[]" multiple disabled>
                                            <option data-placeholder="true"></option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr class="mdc-data-table__row">
                                <td colspan="2" class="mdc-data-table__cell">
                                    <button type="submit" name="add_alias" value="1" class="mdc-button mdc-button--raised mdc-button--icon-trailing">
                                        <span class="mdc-button__ripple"></span>
                                        <i class="material-icons mdc-button__icon" aria-hidden="true">save</i>
                                        <span class="mdc-button__label">Save</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>

        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center mdc-typography--body1">
            <div id="new_names" class="mdc-elevation--z2 mdc-theme--surface mdc-theme--on-surface">
                <h3>👶 New names from the past 6 ops</h3>
                <?php
                foreach ($new_names as $op) {
                    echo '<u>' 
                        // . $op['operation_id] . ' '
                        . substr($op['start_time'], 0, 10) . ' '
                        // . $event_types[$op['event']] . ' '
                        . html_escape($op['mission_name']) 
                        . '</u>';
                    echo '<ul>';
                    foreach ($op['players'] as $id => $name) {
                        echo '<li><span onclick="selectPlayer(' . $id . ');">' . html_escape($name) . '</span></li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        </div>

    </div>
</div>

<script src="<?php echo base_url('public/slimselect.min.js'); ?>"></script>
<script>
    const player_id = <?php echo json_encode($player_id); ?>;
    const players = <?php echo json_encode($players); ?>;
    const player_aliases = players.reduce((res, p) => {
        if (p.alias_of !== undefined && p.uid === undefined) {
            if (res[p.alias_of] === undefined) res[p.alias_of] = [];
            res[p.alias_of].push({
                id: p.id,
                name: p.name
            });
        }
        return res;
    }, {});

    let should_open_aliases_after_change = false;
    const ss_aliases = new SlimSelect({
        select: '#aliases-select',
        closeOnSelect: false,
        allowDeselect: true,
        addToBody: true,
        data: players.reduce((res, p) => {
            if (null !== player_id && p.id !== player_id && (p.alias_of === undefined || p.alias_of === player_id) && p.uid === undefined) {
                res.push({
                    text: p.name,
                    value: p.id,
                    selected: (p.alias_of === player_id ? true : false)
                });
            }
            return res;
        }, [{
            placeholder: true,
            text: ''
        }])
    });

    if (null !== player_id) {
        ss_aliases.enable();
    }

    const ss_player = new SlimSelect({
        select: '#player-select',
        onChange: (sel) => {
            fetch('<?php echo current_url(''); ?>?' + new URLSearchParams({
                    alias_of: sel.value
                }), {
                    method: 'get',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(current_aliases => {
                    const aliases_opts = players.reduce((res, p) => {
                        if (p.id !== sel.value && (p.alias_of === undefined || p.alias_of === sel.value) && p.uid === undefined) {
                            res.push({
                                text: p.name,
                                value: p.id,
                                selected: current_aliases.includes(p.id)
                            });
                        }
                        return res;
                    }, [{
                        placeholder: true,
                        text: ''
                    }]);

                    ss_aliases.setData(aliases_opts);
                    ss_aliases.enable();
                    if (should_open_aliases_after_change) {
                        should_open_aliases_after_change = false;
                        setTimeout(() => {
                            ss_aliases.open();
                        }, 50);
                    }
                });
        },
        addToBody: true,
        data: players.reduce((res, p) => {
            if (p.alias_of === undefined) {
                const aliases = [];
                if (player_aliases[p.id] !== undefined) {
                    player_aliases[p.id].forEach(a => {
                        aliases.push({
                            text: a.name,
                            value: a.id,
                            disabled: true
                        });
                    });
                    res.push({
                        label: p.name,
                        options: [{
                            text: p.name,
                            value: p.id,
                            selected: Boolean(p.id === player_id)
                        }, ...aliases]
                    });
                } else {
                    res.push({
                        text: p.name,
                        value: p.id,
                        selected: Boolean(p.id === player_id),
                    });
                }
            }
            return res;
        }, [{
            placeholder: true,
            text: ''
        }])
    });

    function selectPlayer(value) {
        if (!ss_aliases || !ss_player) return;

        if (ss_aliases.data.contentOpen) {
            ss_aliases.close();
        }

        setTimeout(() => {
            should_open_aliases_after_change = true;
            ss_player.setSelected(value);
        }, ss_aliases.data.contentOpen ? 250 : 10);
    }
</script>