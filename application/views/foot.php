<?php defined('BASEPATH') or exit('No direct script access allowed');

$odata_link = '';
if (defined('OCAP_ODATA_URL')) {
    $odata_url_parts = explode('//', OCAP_ODATA_URL, 2);
    $odata_short_url = $odata_url_parts[count($odata_url_parts)-1];
    $odata_link ='<a href="' . OCAP_ODATA_URL . '">' . $odata_short_url . '</a>';
}

$year_prefix = '';
if ($year !== false) {
    $year_prefix = $year . '/';
}
?>
</main>

<?php if ($odata_link !== ''): ?>
<aside class="mdc-snackbar" data-mdc-auto-init="MDCSnackbar">
    <div class="mdc-snackbar__surface" role="status" aria-relevant="additions">
    <div class="mdc-snackbar__label" aria-atomic="false">
        You can tap into the database directly and create your own custom views and charts by using the OData service at <?php echo $odata_link; ?>.
    </div>
    <div class="mdc-snackbar__actions" aria-atomic="true">
        <button class="mdc-icon-button mdc-snackbar__dismiss material-icons" title="Dismiss">close</button>
    </div>
    </div>
</aside>
<?php endif; ?>

<footer class="mdc-typography--caption">
    <?php if (is_array($years) && count($years) > 1) {
        if (!isset($path)) $path = '';
        echo '<a href="' . base_url($path) . '">';
        if ($year === false) {
            echo '<b>All seasons</b>';
        } else {
            echo 'All seasons';
        }
        echo '</a> &nbsp;&bull;&nbsp; ';
        foreach ($years as $y) {
            echo '<a href="' . base_url($y . '/' . $path) . '">';
            if ($year === $y) {
                echo '<b>' . $y . '</b>';
            } else {
                echo $y;
            }
            echo '</a>';
            if ($y !== end($years)) {
                echo ' &nbsp;&bull;&nbsp; ';
            }
        }
        echo '<br><br>';
    }?>
    <a href="<?php echo base_url($year_prefix . 'about'); ?>">About</a>
    &nbsp;&bull;&nbsp;
    <a href="<?php echo base_url($year_prefix . 'assorted-data'); ?>">Assorted data</a>
    <?php if ($odata_link !== ''): ?>
    &nbsp;&bull;&nbsp;
    <a href="<?php echo OCAP_ODATA_URL; ?>">OData</a>
    <?php endif; ?>
    <br>
    <br>
    <a href="https://github.com/a-sync/ocap-stats" class="github"><svg width="16" height="16" viewBox="0 0 16 16" version="1.1" aria-hidden="true">
            <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path>
        </svg></a>
    <br>
    <span class="render-stats">{elapsed_time}s / {memory_usage}</span>
    <br>
    <span class="render-stats local-timezone"></span>
</footer>

<script>
    document.querySelectorAll('.local-timezone').forEach((el) => {
        el.textContent = Intl.DateTimeFormat().resolvedOptions().timeZone;
    });
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-ts]').forEach((el) => {
            const ts = el.getAttribute('data-ts');
            const date = new Date(ts);
            if (!isNaN(date)) {
                const options = {
                    year: 'numeric', month: 'numeric', day: 'numeric',
                    hour: 'numeric', minute: 'numeric', second: 'numeric',
                    timeZoneName: 'short',
                    hour12: false
                };
                const local = new Intl.DateTimeFormat(undefined, options).format(date);
                const title = el.getAttribute('data-ts-title');
                if (title) {
                    el.title = local;
                } else {
                    el.textContent = local;
                }
            }
        });
    });
</script>
<script src="<?php echo base_url('public/sortable.min.js'); ?>"></script>
<script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
<script type="text/javascript">
    document.addEventListener('MDCAutoInit:End', () => {
        console.log('🖼️');

        <?php if ($odata_link !== ''): ?>
            const odata_snack_ok = localStorage.getItem('odata_snack');
            if (!odata_snack_ok) {
                const snackbar = document.querySelector('.mdc-snackbar').MDCSnackbar;
                snackbar.timeoutMs = -1;
                snackbar.foundation.open();
                snackbar.listen("MDCSnackbar:closed", () => {
                    localStorage.setItem('odata_snack', Date.now());
                });
            }
        <?php endif; ?>
    });

    window.mdc.autoInit();
</script>
</body>

</html>