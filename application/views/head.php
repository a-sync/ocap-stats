<?php defined('BASEPATH') or exit('No direct script access allowed');

?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $title; ?></title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo base_url('public/logo_100_100.png'); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo base_url('public/logo_100_100.png'); ?>" type="image/x-icon">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/4.0.0/github-markdown.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.0/slimselect.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.0/slimselect.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <script src="<?php echo base_url('public/sortable.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo base_url('public/sortable.css'); ?>">

    <link rel="stylesheet" href="<?php echo base_url('public/mdc.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/app.css'); ?>">

</head>

<body class="mdc-typography">

    <?php echo $main_menu; ?>

    <main class="main-content">