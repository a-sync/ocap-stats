<?php defined('BASEPATH') or exit('No direct script access allowed');

?>
<div class="mdc-tab-bar">
    <div class="mdc-tab-scroller">
        <div class="mdc-tab-scroller__scroll-area">
            <div class="mdc-tab-scroller__scroll-content">
                <a href="<?php echo $op_url; ?>" class="mdc-tab<?php if ($active === 'entities') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="true" tabindex="4">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Entities</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'entities') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a>
                <a href="<?php echo $op_url; ?>/events" class="mdc-tab<?php if ($active === 'events') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="5">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Events</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'events') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a>
                <!-- <a href="<?php echo $op_url; ?>/weapons" class="mdc-tab<?php if ($active === 'weapons') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="6">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Weapons</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'weapons') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a> -->
            </div>
        </div>
    </div>
</div>
