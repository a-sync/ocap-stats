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
            <?php echo form_open('', ['id' => 'alias-form']); ?>

            <div class="mdc-text-field mdc-text-field--outlined">
                <input type="text" name="alias_name" value="" class="mdc-text-field__input" tabindex="0">

                <div class="mdc-notched-outline mdc-notched-outline--upgraded">
                    <div class="mdc-notched-outline__leading"></div>
                    <div class="mdc-notched-outline__notch"></div>
                    <div class="mdc-notched-outline__trailing"></div>
                </div>
            </div>

            <div class="mdc-form-field">
                <select id="players-select" name="player_id" multiple>
                    <option data-placeholder="true"></option>
                    <?php foreach ($players as $p) : ?>
                        <option value="<?php echo $p['id'] ?>"><?php echo html_escape($p['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="update_operations" value="1" class="mdc-button mdc-button--raised mdc-button--icon-trailing">
                <span class="mdc-button__ripple"></span>
                <span class="mdc-button__label">Add alias</span>
                <i class="material-icons mdc-button__icon" aria-hidden="true">person_add_alt</i>
            </button>

            <?php echo form_close(); ?>
        </div>


    </div>
</div>
<script>
    new SlimSelect({
        select: '#players-select',
        allowDeselect: true
        // ,searchHighlight: true
    });
</script>