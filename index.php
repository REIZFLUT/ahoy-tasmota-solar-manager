<?php

require __DIR__ . '/app/config.php';

if (isset($_GET['date'])) {
    list($year, $month, $day) = explode('-', $_GET['date']);
} else {
    $year = date('Y');
    $month = date('n');
    $day = date('j');
}

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Überblick</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <?php $page = 'home';
    include __DIR__ . '/app/incl/nav.php'; ?>

    <div class="container mt-5">
        <div class="btn-group" role="group">
            <a href="index.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&mode=day" class="btn btn-dark    <?= (isset($_GET['mode'])) ? (($_GET['mode'] == 'day') ? 'active' : '') : 'active'; ?>">Tag</a>
            <a href="index.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&mode=week" class="btn btn-dark   <?= (isset($_GET['mode'])) ? (($_GET['mode'] == 'week') ? 'active' : '') : ''; ?>">Woche</a>
            <a href="index.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&mode=month" class="btn btn-dark  <?= (isset($_GET['mode'])) ? (($_GET['mode'] == 'month') ? 'active' : '') : ''; ?>">Monat</a>
            <a href="index.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&mode=year" class="btn btn-dark   <?= (isset($_GET['mode'])) ? (($_GET['mode'] == 'year') ? 'active' : '') : ''; ?>">Jahr</a>
            <a href="index.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&mode=decade" class="btn btn-dark <?= (isset($_GET['mode'])) ? (($_GET['mode'] == 'decade') ? 'active' : '') : ''; ?>">Dekade</a>
        </div>
    </div>

    <?php
    $mode = $_GET['mode'] ?? 'day';
    switch ($mode) {
        default:
        case 'day':
            include __DIR__ . '/app/incl/mode_day.php';
            break;
        case 'week':
            include __DIR__ . '/app/incl/mode_week.php';
            break;
        case 'month':
            include __DIR__ . '/app/incl/mode_month.php';
            break;
        case 'year':
            include __DIR__ . '/app/incl/mode_year.php';
            break;
        case 'decade':
            include __DIR__ . '/app/incl/mode_decade.php';
            break;
    }

    ?>


</body>

</html>