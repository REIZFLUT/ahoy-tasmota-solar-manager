<?php
require __DIR__ . '/app/config.php';


use App\Database\Sqlite;

$date = $_GET['date'] ?? 'today';
$resolution = $_GET['resolution'] ?? 60;
$timespan_ts = isset($_GET['ts']) ? ' ' . $_GET['ts'] . ':00' : ' 00:00:00';
$timespan_te = isset($_GET['te']) ? ' ' . $_GET['te'] . ':59' : ' 23:59:59';

$today_start = strtotime($date . $timespan_ts);
$today_end   = strtotime($date . $timespan_te);

$data = Sqlite::query('SELECT * FROM power_log WHERE measured_at >= ? AND measured_at <= ?', [$today_start, $today_end]);

$showTable = $_GET['table'] ?? 0;

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Monitor</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>


    <?php $page = 'home';
    include __DIR__ . '/app/incl/nav.php'; ?>


    <div class="container mt-5">
        <div class="d-flex flex-wrap">
            <div class="btn-group" role="group">
                <span class="btn btn-dark">Auflösung</span>
                <a href="monitor.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&resolution=60&table=<?= $_GET['table'] ?? 0 ?>&ts=<?= $_GET['ts'] ?? '00:00' ?>&te=<?= $_GET['te'] ?? '23:59' ?>" class="btn btn-dark    <?= (isset($_GET['resolution'])) ? (($_GET['resolution'] == '60') ? 'active' : '') : 'active'; ?>">60</a>
                <a href="monitor.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&resolution=6&table=<?= $_GET['table'] ?? 0 ?>&ts=<?= $_GET['ts'] ?? '00:00' ?>&te=<?= $_GET['te'] ?? '23:59' ?>" class="btn btn-dark    <?= (isset($_GET['resolution'])) ? (($_GET['resolution'] == '6')  ? 'active' : '') : ''; ?>">6</a>
                <a href="monitor.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&resolution=1&table=<?= $_GET['table'] ?? 0 ?>&ts=<?= $_GET['ts'] ?? '00:00' ?>&te=<?= $_GET['te'] ?? '23:59' ?>" class="btn btn-dark    <?= (isset($_GET['resolution'])) ? (($_GET['resolution'] == '1') ? 'active' : '') : ''; ?>">1</a>
            </div>
            <div class="ms-4">
                <a href="monitor.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>&resolution=<?= $_GET['resolution'] ?? 60 ?>&table=<?= (isset($_GET['table'])) ? (($_GET['table'] == '1')  ? '0' : '1') : '1'; ?>&ts=<?= $_GET['ts'] ?? '00:00' ?>&te=<?= $_GET['te'] ?? '23:59' ?>" class="btn btn-dark <?= (isset($_GET['table'])) ? (($_GET['table'] == '1')  ? 'active' : '') : ''; ?>">Wertetabelle <?= (isset($_GET['table'])) ? (($_GET['table'] == '1')  ? 'ausblenden' : 'anzeigen') : 'anzeigen'; ?></a>
            </div>
            <div class="ms-4">
                <form method="GET" id="timespan_form">
                    <div class="input-group">

                        <input type="hidden" name="date" value="<?= $_GET['date'] ?? date('Y-m-d') ?>">
                        <input type="hidden" name="resolution" value="<?= $_GET['resolution'] ?? 60 ?>">
                        <input type="hidden" name="table" value="<?= $_GET['table'] ?? 0 ?>">

                        <span class="input-group-text" id="zeitfenster-start">Zeitfenster</span>
                        <input type="time" name="ts" id="timespan_ts" class="form-control" placeholder="HH:MM" aria-label="Startzeit" aria-describedby="zeitfenster-start" value="<?= $_GET['ts'] ?? '00:00'; ?>">
                        <span class="input-group-text" id="zeitfenster-ende">bis</span>
                        <input type="time" name="te" id="timespan_te" class="form-control" placeholder="HH:MM" aria-label="Endzeit" aria-describedby="zeitfenster-ende" value="<?= $_GET['te'] ?? '23:59' ?>">
                    </div>
                </form>
                <script>
                    const timespan_start = document.getElementById('timespan_ts');
                    const timespan_end = document.getElementById('timespan_te');
                    const timespan_form = document.getElementById('timespan_form');
                    timespan_start.addEventListener('change', (e) => {
                        timespan_form.submit();
                    })

                    timespan_end.addEventListener('change', (e) => {
                        timespan_form.submit();
                    })
                </script>



            </div>
        </div>
    </div>


    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Messung', 'Netz', 'Solar', 'Verbrauch'],
                <?php

                $dn = count($data);
                $data_points = [];
                $j = 0;

                if (count($data)) {
                    for ($i = 0; $i < $dn; $i++) {
                        if ($resolution == 1) {
                            echo "['" . date('d.m.Y H:i:s', $data[$i]['measured_at']) . "', " .
                                $data[$i]['power_grid'] . ", " . $data[$i]['power_solar'] . ", " .
                                ($data[$i]['power_grid'] + $data[$i]['power_solar']) . "], \n";
                        } else {
                            if (($i % $resolution)) {
                                $data_points[$data[$i]['measured_at']]['measured_at'] = $data[$i]['measured_at'];
                                $data_points[$data[$i]['measured_at']]['power_grid'] = $data[$i]['power_grid'];
                                $data_points[$data[$i]['measured_at']]['power_solar'] = $data[$i]['power_solar'];
                            } else {
                                $data_points[$data[$i]['measured_at']]['measured_at'] = $data[$i]['measured_at'];
                                $data_points[$data[$i]['measured_at']]['power_grid'] = $data[$i]['power_grid'];
                                $data_points[$data[$i]['measured_at']]['power_solar'] = $data[$i]['power_solar'];

                                $output_prep = [];
                                $output_prep['pgs'] = 0;
                                $output_prep['pss'] = 0;

                                foreach ($data_points as $dp) {
                                    if (!isset($output_prep['ma'])) {
                                        $output_prep['ma'] = intval($dp['measured_at']);
                                    }
                                    $output_prep['pgs'] += $dp['power_grid'];
                                    $output_prep['pss'] += $dp['power_solar'];
                                }

                                $output_prep['pg'] = $output_prep['pgs'] / $resolution;
                                $output_prep['ps'] = $output_prep['pss'] / $resolution;

                                echo "['" . date('d.m.Y H:i:s', $output_prep['ma']) . "', " .
                                    $output_prep['pg'] . ", " . $output_prep['ps'] . ", " .
                                    ($output_prep['pg'] + $output_prep['ps']) . "], \n";


                                $data_points = [];
                            }
                        }
                    }
                } else {
                    echo "['" . date('d.m.Y H:i:s', $today_start) . "', " .
                        0 . ", " . 0 . ", " . 0 . "], \n";
                }


                ?>
            ]);

            var options = {
                title: 'Leistung in Watt',
                curveType: 'function',
                colors: ['darkblue', 'orange', 'red'],
                legend: {
                    position: 'bottom'
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            chart.draw(data, options);
        }
    </script>

    <div class="container-fluid">
        <div id="curve_chart" style="width: 100%; height: 800px"></div>
    </div>



    <?php if ($showTable) : ?>
        <div class="container mt-5">

            <?php if (count($data)) : ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Messung</th>
                            <th>Leistung Netz</th>
                            <th>Leistung Solar</th>
                            <th>Inv. Lim. IST</th>
                            <th>Inv. Lim. SOLL</th>
                            <th>Aktion</th>
                            <th>Zähler Verb.</th>
                            <th>Zähler Einsp.</th>
                            <th>Solar Zähler</th>
                            <th>Temp.</th>
                        </tr>
                    </thead>


                    <tbody>
                        <?php

                        $power_grid = 0;
                        $i = 0;
                        foreach (array_reverse($data) as $log) {

                            $power_grid += $log['power_grid'];
                            $i++;

                            $states = ['-', 'Steuerung', 'offline'];

                            echo
                            '
                            <tr>
                                <td>' . $log['id'] . '</td>
                                <td>' . date('d.m.Y H:i:s', $log['measured_at']) . '</td>
                                <td class="text-end">' . $log['power_grid'] . ' W</td>
                                <td class="text-end">' . $log['power_solar'] . ' W</td>
                                <td class="text-end">' . $log['inverter_power_limit_is'] . ' %</td>
                                <td class="text-end">' . $log['inverter_power_limit_adjust'] . ' %</td>
                                <td class="text-center">' . $states[$log['inverter_power_limit_state']] . '</td>
                                <td class="text-end">' . number_format(round($log['grid_counter_in'] / 1000, 2), 2) . '</td>
                                <td class="text-end">' . number_format(round($log['grid_counter_out'] / 1000, 2), 2) . '</td>
                                <td class="text-end">' . number_format(round($log['inverter_total'] / 100, 2), 2) . '</td>
                                <td class="text-end">' . number_format(round($log['inverter_temp'] / 100, 2), 2) . '</td>
                            </tr>
                            ' . "\n";
                        }

                        ?>
                    </tbody>

                </table>
            <?php else : ?>
                <div class="alert alert-info">
                    Für den angegebenen Zeitraum sind keine Daten vorhanden.
                </div>
            <?php endif; ?>
        </div>
    <? endif; ?>

</body>

</html>