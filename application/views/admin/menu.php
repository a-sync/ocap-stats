<?php defined('BASEPATH') or exit('No direct script access allowed');

?>
<header>

    <div class="mdc-tab-bar" role="tablist">
        <div class="mdc-tab-scroller">
            <div class="mdc-tab-scroller__scroll-area">
                <div class="mdc-tab-scroller__scroll-content">
                    <a href="<?php echo base_url('manage'); ?>" class="mdc-tab" role="tab" aria-selected="true" tabindex="1">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">checklist_rtl</span>
                            <span class="mdc-tab__text-label">Operations</span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                    <a href="<?php echo base_url('add-alias'); ?>" class="mdc-tab" role="tab" aria-selected="true" tabindex="2">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">reduce_capacity</span>
                            <span class="mdc-tab__text-label">Add player alias</span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                    <a href="<?php echo base_url('fix-data'); ?>" class="mdc-tab" role="tab" aria-selected="true" tabindex="3">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">healing</span>
                            <span class="mdc-tab__text-label">Fix missing op data</span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                    <a href="<?php echo base_url('logout'); ?>" class="mdc-tab mdc-tab--active" role="tab" aria-selected="true" tabindex="4">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">logout</span>
                            <span class="mdc-tab__text-label">Logout</span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</header>