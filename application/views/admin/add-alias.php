<?php defined('BASEPATH') or exit('No direct script access allowed');

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
                    <?php echo form_open('', ['id' => 'alias-form']); ?>
                    <table class="mdc-data-table__table">
                        <tbody class="mdc-data-table__content">
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
                                        <span class="mdc-button__label">Save</span>
                                        <i class="material-icons mdc-button__icon" aria-hidden="true">save</i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>


    </div>
</div>

<script src="<?php echo base_url('public/slimselect.min.js'); ?>"></script>
<script>
    const player_id = <?php echo json_encode($player_id); ?>;
    const players = <?php echo json_encode($players); ?>;

    const ss_aliases = new SlimSelect({
        select: '#aliases-select',
        allowDeselect: true,
        addToBody: true,
        data: players.reduce((res, p) => {
            if (null !== player_id && (parseInt(p.alias_of, 2) === 0 || p.alias_of === player_id)) {
                res.push({
                    text: p.name,
                    value: p.id,
                    selected: (p.alias_of === player_id ? true : false)
                });
            }
            return res;
        }, [{placeholder:true,text:''}])
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
                        if (p.id !== sel.value && (parseInt(p.alias_of, 2) === 0 || p.alias_of === sel.value)) {
                            res.push({
                                text: p.name,
                                value: p.id,
                                selected: current_aliases.includes(p.id)
                            });
                        }
                        return res;
                    }, [{placeholder:true,text:''}]);

                    ss_aliases.setData(aliases_opts);
                    ss_aliases.enable();
                });
        },
        addToBody: true,
        data: players.reduce((res, p) => {
            if (parseInt(p.alias_of, 2) === 0) {
                res.push({
                    text: p.name,
                    value: p.id,
                    selected: (p.id === player_id ? true : false)
                });
            }
            return res;
        }, [{placeholder:true,text:''}])
    });
</script>
