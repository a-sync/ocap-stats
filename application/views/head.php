<?php defined('BASEPATH') or exit('No direct script access allowed');

$icon_url = base_url($this->config->item('site_logo'));
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $title; ?></title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo $icon_url; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $icon_url; ?>" type="image/x-icon">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/4.0.0/github-markdown.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="<?php echo base_url('public/mdc.min.css'); ?>">

    <link rel="stylesheet" href="<?php echo base_url('public/slimselect.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/sortable.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/app.css'); ?>">
</head>

<body class="mdc-typography">

    <?php echo $main_menu; ?>

<!--odata snackbar-->
    <style>
    @media (prefers-color-scheme: light) {
        .mdc-snackbar__label a {
            color: #9553f2;
        }
        .mdc-snackbar__label a:visited {
            color: var(--mdc-theme-secondary) !important;
        }
    }
    .mdc-snackbar__dismiss {
        color: var(--mdc-theme-error);
    }
    </style>
    <aside class="mdc-snackbar"><!-- data-mdc-auto-init="MDCSnackbar"-->
      <div class="mdc-snackbar__surface" role="status" aria-relevant="additions">
        <div class="mdc-snackbar__label" aria-atomic="false">
          Good news everyone!<br>You can tap into the database directly and create your own custom views and charts by using the new OData service at <a href="https://fnf-odata.devs.space">fnf-odata.devs.space</a>.
        </div>
        <div class="mdc-snackbar__actions" aria-atomic="true">
            <button class="mdc-icon-button mdc-snackbar__dismiss material-icons" title="Dismiss">🗙</button>
        </div>
      </div>
    </aside>
    <script>
    window.addEventListener('load', () => {
        const odata_snack_ok = localStorage.getItem('odata_snack');
        if (!odata_snack_ok) {
            const snackbar = new window.mdc.snackbar.MDCSnackbar(document.querySelector('.mdc-snackbar'));
            snackbar.timeoutMs = -1;
            snackbar.foundation.open();
            snackbar.listen("MDCSnackbar:closed", () => {
                localStorage.setItem('odata_snack', Date.now());
            });
        }
    });
    </script>
<!--/odata snackbar-->

    <main class="main-content">