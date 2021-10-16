<?php defined('BASEPATH') or exit('No direct script access allowed');

?>

<div class="mdc-layout-grid">
    <div class="mdc-layout-grid__inner">


        <?php if (count($errors) > 0): ?>
            <div class="errors mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <h3>⚠️ Errors</h3>
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12 flex--center">
            <?php echo form_open('', ['id' => 'commanders-form']); ?>
            
            <?php echo form_close(); ?>
        </div>


    </div>
</div>