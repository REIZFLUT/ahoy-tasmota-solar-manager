<?php

use App\Database\Sqlite;

require __DIR__ . '/../app/config.php';

if (isset($_GET['date'])) {
    list($year, $month, $day) = explode('-', $_GET['date']);
} else {
    $year = date('Y');
    $month = date('n');
    $day = date('j');
}

if(count($_POST)){
    Sqlite::updateById('power_stat_hourly', intval($_GET['id']), $_POST);
    header('Location: stat_editor.php');
}

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Überblick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <?php $page = 'home';
    include __DIR__ . '/../app/incl/nav.php'; ?>

    <?php if (isset($_GET['id'])) :
        $psr = Sqlite::selectById('power_stat_hourly', intval($_GET['id']));
        if ($psr) :

    ?>
            <div class="container mt-5 pb-5">
                <form method="POST">
                    <?php foreach ($psr as $pkey => $pval) :
                        if ($pkey != 'id') : ?>
                            <div class="mb-2">
                                <label><?= $pkey ?></label>
                                <input type="number" value="<?= $pval ?>" name="<?= $pkey; ?>" class="form-control" required>
                            </div>

                    <?php endif;
                    endforeach; ?>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        <?php endif;
    else : ?>
        <form method="GET" class="from">
            <div class="container mt-3">
                <label>Energiestatistik ID</label>
                <div class="input-group mt-2">
                    <input type="number" name="id" class="form-control" min="1" step="1">
                    <button class="btn btn-primary">Bearbeiten</button>
                </div>
            </div>
        </form>
    <?php endif; ?>


</body>

</html>