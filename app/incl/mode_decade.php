<?php

use App\Database\Sqlite;

$curr_date_st = $_GET['date'] ?? date('Y-n-j');
list($curr_year, $curr_month, $curr_day) = explode('-', $curr_date_st);

$curr_lastyear  = mktime(0, 0, 0, 1, 1, $curr_year);

$year_results = [];

$grid_cons_sum = 0;
$grid_feed_sum = 0;
$solar_sum = 0;

for ($i = 0; $i < 10; $i++) {
    $sub_seconds = $i * 31536000;
    $curr_day = $curr_lastyear - $sub_seconds;
    list($year, $month, $day) = explode('-', date('Y-n-j', $curr_day));


    $result = Sqlite::query('SELECT * FROM power_stat_hourly WHERE year = ?', [$year]);
    $rn = count($result);
    $rm = $rn - 1;



    $year_results[$i]['y'] = date('Y', $curr_day);

    if ($rn) {
        $year_results[$i]['grid']  = ($result[$rm]['grid_c_end'] - $result[0]['grid_c_start']) / 1000;
        $year_results[$i]['feed']  = ($result[$rm]['grid_f_end'] - $result[0]['grid_f_start']) / 1000 * -1;
        $year_results[$i]['solar'] = ($result[$rm]['inv_total_end'] - $result[0]['inv_total_start']) / 100;

        $grid_cons_sum += $year_results[$i]['grid'];
        $grid_feed_sum += $year_results[$i]['feed'];
        $solar_sum += $year_results[$i]['solar'];

    } else {
        $year_results[$i]['grid'] = 0;
        $year_results[$i]['feed'] = 0;
        $year_results[$i]['solar'] = 0;
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
        data.addColumn('string', 'Monat');
        data.addColumn('number', 'Einspeisung');
        data.addColumn('number', 'Solar');
        data.addColumn('number', 'Netzbezug');


        data.addRows([
            <?php foreach (array_reverse($year_results) as $dr) : ?>[{
                    v: '<?= $dr['y'] ?>'
                }, <?= $dr['feed'] ?>, <?= $dr['solar'] + $dr['feed']  ?>, <?= $dr['grid'] ?>],
            <?php endforeach; ?>
        ]);


        var options = {
            backgroundColor: '#EFEFEF',
            title: 'Energiekonsum kWh',
            colors: ['purple', 'orange', 'darkblue'],
            isStacked: true,
            hAxis: {
                title: 'Jahr'
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
            Werte der letzten 10 Jahre bis <?= date('Y', $curr_lastyear); ?>
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