<?php

use App\Database\Sqlite;

$curr_date_st = $_GET['date'] ?? date('Y-n-j');
list($curr_year, $curr_month, $curr_day) = explode('-', $curr_date_st);

$curr_date_ts = mktime(0, 0, 0, $curr_month, $curr_day, $curr_year);
$curr_monday  = strtotime('monday this week', $curr_date_ts);
//echo date('d.m.Y H:i:s', $curr_date_ts);

$day_results = [];
$day_names = ['Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.', 'So.'];

$grid_cons_sum = 0;
$grid_feed_sum = 0;
$solar_sum = 0;

for ($i = 0; $i < 7; $i++) {
    $add_seconds = $i * 86400;
    $curr_day = $curr_monday + $add_seconds;
    list($year, $month, $day) = explode('-', date('Y-n-j', $curr_day));

    $result = Sqlite::query('SELECT * FROM power_stat_hourly WHERE year = ? AND month = ? AND day = ? ORDER BY hour ASC', [$year, $month, $day]);
    $rn = count($result);
    $rm = $rn - 1;

    $day_results[$i]['wd'] = $day_names[$i] . ', ' . date('d.m.Y', $curr_day);

    if ($rn) {
        $day_results[$i]['grid']  = round(($result[$rm]['grid_c_end'] - $result[0]['grid_c_start']) / 1000, 2);
        $day_results[$i]['feed']  = round(($result[$rm]['grid_f_end'] - $result[0]['grid_f_start']) / 1000, 2) * -1;
        $day_results[$i]['solar'] = round(($result[$rm]['inv_total_end'] - $result[0]['inv_total_start']) / 100, 2);

        $grid_cons_sum += $day_results[$i]['grid'];
        $grid_feed_sum += $day_results[$i]['feed'];
        $solar_sum += $day_results[$i]['solar'];

    } else {
        $day_results[$i]['grid'] = 0;
        $day_results[$i]['feed'] = 0;
        $day_results[$i]['solar'] = 0;
    }
}

?>


<script>
    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawStacked);

    function drawStacked() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Woche');
        data.addColumn('number', 'Einspeisung');
        data.addColumn('number', 'Solar');
        data.addColumn('number', 'Netzbezug');


        data.addRows([
            <?php foreach ($day_results as $dr) : ?>[{
                    v: '<?= $dr['wd'] ?>'
                }, <?= $dr['feed'] ?>, <?= $dr['solar'] + $dr['feed'] ?>, <?= $dr['grid'] ?>],
            <?php endforeach; ?>
        ]);


        var options = {
            backgroundColor: '#EFEFEF',
            title: 'Energiekonsum kWh',
            colors: ['purple', 'orange', 'darkblue'],
            isStacked: true,
            hAxis: {
                title: 'Tag'
            },
            vAxis: {
                title: 'Verbrauch kWh'
            }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>

<div class="hero bg-body-tertiary my-5">
    <div id="chart_div" style="width: 100%; height: 500px"></div>
</div>


<div class="container mt-5">
    <div class="card mb-4">
        <div class="card-header">
            Werte in der Woche vom  <?= date('d.m.Y', $curr_monday) ?> bis <?= date('d.m.Y', $curr_monday + 604799) ?> (KW <?= date('W', $curr_monday) ?> )
        </div>
        <div class="card-body">
        <table class="table">
                <tbody>
                    <tr>
                        <td>Netzverbrauch:</td>
                        <td class="text-end"><?= round($grid_cons_sum, 2); ?> kWh</td>
                    </tr>
                    <tr>
                        <td>Solarerzeugung:</td>
                        <td class="text-end"><?= round($solar_sum, 2); ?> kWh</td>
                    </tr>                    
                    <tr>
                        <td>Solarverbrauch:</td>
                        <td class="text-end"><?= round($solar_sum + $grid_feed_sum, 2); ?> kWh</td>
                    </tr>
                    <tr>
                        <td>Verbrauch ges.:</td>
                        <td class="text-end"><?= round(($grid_cons_sum + $solar_sum + $grid_feed_sum) , 2); ?> kWh</td>
                    </tr>
                    <tr>
                        <td>Rückspeisung ges.:</td>
                        <td class="text-end"><?= round($grid_feed_sum * -1, 2) ?> kWh</td>
                    </tr>
                    <tr>
                        <td>Netzverbr. Kosten (<?= $GLOBALS['CONFIG']['EnergyTariff']; ?> €/kWh):</td>
                        <td class="text-end"><?= round($grid_cons_sum * $GLOBALS['CONFIG']['EnergyTariff'], 2) ?> €</td>
                    </tr>
                    <tr>
                        <td>Gespart durch Solar (<?= $GLOBALS['CONFIG']['EnergyTariff']; ?> €/kWh):</td>
                        <td class="text-end"><?= round(($solar_sum + $grid_feed_sum) * $GLOBALS['CONFIG']['EnergyTariff'], 2); ?> €</td>
                    </tr>
                    <tr>
                        <td>Solaranteil am Verbrauch:</td>
                        <td class="text-end"><?= (($grid_cons_sum + $solar_sum) > 0) ? round(($solar_sum + $grid_feed_sum) / ($grid_cons_sum + $solar_sum + $grid_feed_sum) * 100, 2) : 0;  ?> %</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>