<?php
defined('BASEPATH') or exit('No direct script access allowed');

$event_types = $this->config->item('event_types');

if (count($event_types) > 1) :
    echo form_open('', [
        'id' => 'filter-form',
        'method' => 'get'
    ]);

    foreach ($event_types as $id => $name) :
        $extra_attr = '';
        if (is_array($events_filter) && in_array($id, $events_filter)) {
            $extra_attr = ' checked';
        }
?>

        <div class="mdc-form-field filters_checkbox">
            <div class="mdc-touch-target-wrapper">
                <div class="mdc-checkbox mdc-checkbox--touch">
                    <input type="checkbox" class="mdc-checkbox__native-control" id="<?php echo 'event-' . $id; ?>" name="events[]" value="<?php echo $id; ?>" <?php echo $extra_attr; ?>>
                    <div class="mdc-checkbox__background">
                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59">
                        </svg>
                        <div class="mdc-checkbox__mixedmark"></div>
                    </div>
                    <div class="mdc-checkbox__ripple"></div>
                </div>
            </div>
            <label for="<?php echo 'event-' . $id; ?>"><?php echo html_escape($name); ?></label>
        </div>
    <?php endforeach; ?>

    <div class="mdc-form-field filters_submit">
        <button class="mdc-button mdc-button--raised">
            <span class="mdc-button__ripple"></span>
            <span class="mdc-button__focus-ring"></span>
            <span class="mdc-button__label">Apply</span>
        </button>
    </div>

<?php
    echo form_close();

endif;
