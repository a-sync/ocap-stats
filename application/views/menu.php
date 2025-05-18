<?php defined('BASEPATH') or exit('No direct script access allowed');

$year_prefix = '';
if ($year !== false) {
    $year_prefix = $year . '/';
}
?>
<header>

    <div class="mdc-tab-bar" role="tablist">
        <div class="mdc-tab-scroller">
            <div class="mdc-tab-scroller__scroll-area">
                <div class="mdc-tab-scroller__scroll-content">
                    <a href="<?php echo base_url($year_prefix . 'players'); ?>" class="mdc-tab<?php if ($active === 'players') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="true" tabindex="1">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">people</span>
                            <span class="mdc-tab__text-label">Players</span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                    <a href="<?php echo base_url(rtrim($year_prefix, '/')); ?>" class="mdc-tab<?php if ($active === 'ops') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="true" tabindex="2">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">calendar_month</span>
                            <span class="mdc-tab__text-label">Ops</span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                    <a href="<?php echo base_url($year_prefix . 'commanders'); ?>" class="mdc-tab<?php if ($active === 'commanders') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="true" tabindex="3">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">military_tech</span>
                            <span class="mdc-tab__text-label">Commanders</span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</header>