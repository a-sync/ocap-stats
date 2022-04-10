<?php defined('BASEPATH') or exit('No direct script access allowed');

?>

<div class="mdc-tab-bar">
    <div class="mdc-tab-scroller">
        <div class="mdc-tab-scroller__scroll-area">
            <div class="mdc-tab-scroller__scroll-content">
                <a href="<?php echo $player_url; ?>" class="mdc-tab<?php if ($active === 'ops') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="true" tabindex="4">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Ops</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'ops') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a>
                <a href="<?php echo $player_url; ?>/roles" class="mdc-tab<?php if ($active === 'roles') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="5">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Roles</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'roles') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a>
                <a href="<?php echo $player_url; ?>/weapons" class="mdc-tab<?php if ($active === 'weapons') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="6">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Weapons</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'weapons') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a>
                <a href="<?php echo $player_url; ?>/attackers" class="mdc-tab<?php if ($active === 'attackers') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="7">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Attackers</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'attackers') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a>
                <a href="<?php echo $player_url; ?>/victims" class="mdc-tab<?php if ($active === 'victims') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="8">
                    <span class="mdc-tab__content">
                        <span class="mdc-tab__text-label">Victims</span>
                    </span>
                    <span class="mdc-tab-indicator<?php if ($active === 'victims') echo ' mdc-tab-indicator--active';  ?>">
                        <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                    </span>
                    <span class="mdc-tab__ripple"></span>
                </a>
                <?php if($show_rivals): ?>
                    <a href="<?php echo $player_url; ?>/rivals" class="mdc-tab<?php if ($active === 'rivals') echo ' mdc-tab--active';  ?>" role="tab" aria-selected="false" tabindex="9">
                        <span class="mdc-tab__content">
                            <span class="mdc-tab__icon material-icons" aria-hidden="true">military_tech</span>
                            <span class="mdc-tab__text-label">Rivals</span>
                        </span>
                        <span class="mdc-tab-indicator<?php if ($active === 'rivals') echo ' mdc-tab-indicator--active';  ?>">
                            <span class="mdc-tab-indicator__content mdc-tab-indicator__content--underline"></span>
                        </span>
                        <span class="mdc-tab__ripple"></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>