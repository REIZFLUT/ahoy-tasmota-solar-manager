<script>
    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawStacked);

    function drawStacked() {
        var data = new google.visualization.DataTable();
        data.addColumn('timeofday', 'Uhrzeit');
        data.addColumn('number', 'Einspeisung');
        data.addColumn('number', 'Solar');
        data.addColumn('number', 'Netzbezug');


        data.addRows([
            <?php

            use App\Database\Sqlite;

            $power_stat_hourly = Sqlite::query('SELECT * FROM power_stat_hourly WHERE year = ? AND month = ? AND day = ?', [$year, $month, $day]);

            $energy_grid_day  = 0;
            $energy_solar_day = 0;
            $energy_feed_day = 0;

            foreach ($power_stat_hourly as $ps) {
                $energy_grid = $ps['grid_c_end'] - $ps['grid_c_start'];
                $energy_feedback = ($ps['grid_f_end'] - $ps['grid_f_start']) * -1;
                $energy_feed_day += (($ps['grid_f_end'] - $ps['grid_f_start']));
                $energy_solar = (($ps['inv_total_end'] - $ps['inv_total_start']) * 10);
                $energy_total = $energy_grid + $energy_solar + $energy_feedback;
                $energy_grid_perc = round($energy_grid / $energy_total * 100, 2);
                $energy_solar_perc = round($energy_solar / $energy_total * 100, 2);
                $energy_grid_day += $energy_grid;
                $energy_solar_day += $energy_solar;

                echo '[{v: [' . $ps['hour'] . ', 0, 0], f: "Gesamt: ' . $energy_total . ' Wh"}, ' . $energy_feedback . ', ' . $energy_solar + $energy_feedback . ", " . $energy_grid . "],\n";
            }

            $energy_total_day = $energy_grid_day + $energy_solar_day - $energy_feed_day;

            ?>
        ]);

        var options = {
            backgroundColor: '#EFEFEF',
            title: 'Energiekonsum Wh',
            colors: ['purple', 'orange', 'darkblue'],
            isStacked: true,
            hAxis: {
                title: 'Uhrzeit',
                format: 'HH:mm',
                viewWindow: {
                    min: [0, 0, 0],
                    max: [23, 0, 0]
                }
            },
            vAxis: {
                title: 'Verbrauch Wh'
            }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>


<div class="hero bg-body-tertiary my-5">
    <div id="chart_div" style="width: 100%; height: 500px"></div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-12 col-lg-6 mb-4">
            <div class="card mb-4">
                <div class="card-header">
                    Werte am <?= $day . '.' . $month . '.' . $year; ?>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Datum:</td>
                                <td class="text-end"><?= $day . '.' . $month . '.' . $year; ?></td>
                            </tr>
                            <tr>
                                <td>Netzverbrauch:</td>
                                <td class="text-end"><?= round($energy_grid_day / 1000, 2); ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Solarerzeugung:</td>
                                <td class="text-end"><?= round(($energy_solar_day) / 1000, 2); ?> kWh</td>
                            </tr>                           
                            <tr>
                                <td>Solarverbrauch:</td>
                                <td class="text-end"><?= round(($energy_solar_day - $energy_feed_day) / 1000, 2); ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Verbrauch ges.:</td>
                                <td class="text-end"><?= round(($energy_total_day) / 1000, 2); ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Rückspeisung ges.:</td>
                                <td class="text-end"><?= round($energy_feed_day / 1000, 2) ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Netzverbr. Kosten (<?= $GLOBALS['CONFIG']['EnergyTariff']; ?> €/kWh):</td>
                                <td class="text-end"><?= round($energy_grid_day / 1000 * $GLOBALS['CONFIG']['EnergyTariff'], 2) ?> €</td>
                            </tr>
                            <tr>
                                <td>Ersparnis (<?= $GLOBALS['CONFIG']['EnergyTariff']; ?> €/kWh):</td>
                                <td class="text-end"><?= round(($energy_solar_day - $energy_feed_day)/ 1000 * $GLOBALS['CONFIG']['EnergyTariff'], 2); ?> €</td>
                            </tr>
                            <tr>
                                <td>Solaranteil am Verbrauch:</td>
                                <td class="text-end"><?= ($energy_total_day > 0) ? round(($energy_solar_day - $energy_feed_day) / $energy_total_day * 100, 2) : 0;  ?> %</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card mb-4">

                <?php
                $power_stat_hourly_first = Sqlite::query('SELECT * FROM power_stat_hourly ORDER BY id ASC LIMIT 0,1')[0];
                $power_stat_hourly_last  = Sqlite::query('SELECT * FROM power_stat_hourly ORDER BY id DESC LIMIT 0,1')[0];
                $power_stat_running_days = intval(round((mktime(0, 0, 0, $power_stat_hourly_last['month'], $power_stat_hourly_last['day'], $power_stat_hourly_last['year']) -
                    mktime(0, 0, 0, $power_stat_hourly_first['month'], $power_stat_hourly_first['day'], $power_stat_hourly_first['year'])) /
                    86400));
                $power_stat_running_grid = round(($power_stat_hourly_last['grid_c_end'] - $power_stat_hourly_first['grid_c_start']) / 1000, 2);
                $power_stat_running_feed = round(($power_stat_hourly_last['grid_f_end'] - $power_stat_hourly_first['grid_f_start']) / 1000, 2);
                $power_stat_running_solar = round(($power_stat_hourly_last['inv_total_end'] - $power_stat_hourly_first['inv_total_start']) / 100, 2);

                ?>


                <div class="card-header">
                    Werte seit Inbetriebnahme
                </div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Erste Erfassung:</td>
                                <td class="text-end"><?= ($power_stat_hourly_first['day'] < 10 ? '0' . $power_stat_hourly_first['day'] : $power_stat_hourly_first['day']) . '.' . ($power_stat_hourly_first['month'] < 10 ? '0' . $power_stat_hourly_first['month'] : $power_stat_hourly_first['month']) . '.' . $power_stat_hourly_first['year'] ?></td>
                            </tr>
                            <tr>
                                <td>In Betrieb:</td>
                                <td class="text-end"><?= $power_stat_running_days; ?> Tage</td>
                            </tr>
                            <tr>
                                <td>Netzverbrauch: </td>
                                <td class="text-end"><?= $power_stat_running_grid ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Solar-Erzeugung:</td>
                                <td class="text-end"><?= $power_stat_running_solar; ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Solar-Verbrauch:</td>
                                <td class="text-end"><?= $power_stat_running_solar - $power_stat_running_feed; ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Verbrauch gesamt:</td>
                                <td class="text-end"><?= $power_stat_running_grid + $power_stat_running_solar - $power_stat_running_feed; ?> kWh</td>
                            </tr>
                            <tr>
                                <td>Rückspeisung:</td>
                                <td class="text-end"><?= $power_stat_running_feed; ?> kWh</td>
                            </tr>   
                            <tr>
                                <td>Netzverbr. Kosten (<?= $GLOBALS['CONFIG']['EnergyTariff']; ?> € / kwH):</td>
                                <td class="text-end"><?= round($power_stat_running_grid * $GLOBALS['CONFIG']['EnergyTariff'], 2); ?> €</td>
                            </tr>                                                 
                            <tr>
                                <td>Ersparnis (<?= $GLOBALS['CONFIG']['EnergyTariff']; ?> € / kwH):</td>
                                <td class="text-end"><?= round(($power_stat_running_solar - $power_stat_running_feed) * $GLOBALS['CONFIG']['EnergyTariff'], 2); ?> €</td>
                            </tr>
                            <tr>
                                <td>Ersparnis CO2 t (700g / kwH):</td>
                                <td class="text-end"><?= round($power_stat_running_solar * 0.0007, 2); ?> t</td>
                            </tr>
                            <tr>
                                <td>Solaranteil am Verbrauch:</td>
                                <td class="text-end"><?= round(($power_stat_running_solar - $power_stat_running_feed) / ($power_stat_running_grid + $power_stat_running_solar - $power_stat_running_feed) * 100, 2); ?> %</td>
                            </tr>    

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    Details am <?= $day . '.' . $month . '.' . $year; ?>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-end">Uhr</th>
                                <th class="text-end" style="background-color: darkblue; color:#FFF;">Netz</th>
                                <th class="text-end" style="background-color: orange;">Solar</th>
                                <th class="text-end" style="background-color: purple; color:#FFF;">Rücksp.</th>
                                <th class="text-end">Verbrauch</th>
                                <th class="text-end">% Solar</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($power_stat_hourly as $psh) :
                                $energy_grid = $psh['grid_c_end'] - $psh['grid_c_start'];
                                $energy_feedback = ($psh['grid_f_end'] - $psh['grid_f_start']);
                                $energy_solar = (($psh['inv_total_end'] - $psh['inv_total_start']) * 10) - $energy_feedback;
                                $energy_total = $energy_grid + $energy_solar;
                                $energy_grid_perc = ($energy_total > 0) ? round($energy_grid / $energy_total * 100, 2):0;
                                $energy_solar_perc = ($energy_total > 0) ? (round($energy_solar / $energy_total * 100, 2)):0;


                            ?>
                                <tr>
                                    <td class="text-end"><?= ($psh['hour'] < 10) ? '0' . $psh['hour'] : $psh['hour']; ?></td>
                                    <td class="text-end" style="background-color: darkblue; color:#FFF;"><?= $energy_grid; ?> Wh</td>
                                    <td class="text-end" style="background-color: orange;"><?= $energy_solar; ?> Wh</td>
                                    <td class="text-end" style="background-color: purple; color:#FFF;"><?= $energy_feedback; ?> Wh</td>
                                    <td class="text-end"><?= $energy_total; ?> Wh</td>
                                    <td class="text-end"><?= number_format($energy_solar_perc, 2) ?> %</td>
                                    <td class="text-end"><a href="#" class="detailsModalBtn" <?php foreach ($psh as $psh_key => $psh_val) {
                                                                                                    echo 'data-' . $psh_key . '="' . $psh_val . '" ';
                                                                                                } ?>>Details</a>


                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="detailsModalLabel">Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped">
                    <tbody id="detailsList">
                        <tr>
                            <td>Stunden-Statistik Nr.</td>
                            <td id="detail_id" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Messzeitraum (1 Stunde)</td>
                            <td class="text-end"><span id="detail_day"></span>.<span id="detail_month"></span>.<span id="detail_year"></span>,
                                <span id="detail_hour"></span> Uhr
                            </td>
                        </tr>
                        <tr>
                            <td>Anzahl der Messungen im Zeitraum</td>
                            <td id="detail_measure_cnt" class="text-end"></td>
                        </tr>                        
                        <tr>
                            <td>Zählerstand Bezug Anfang (kWh)</td>
                            <td id="detail_grid_c_start" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Zählerstand Bezug Ende (kWh)</td>
                            <td id="detail_grid_c_end" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Zählerstand Einspeisung Anfang (kWh)</td>
                            <td id="detail_grid_f_start" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Zählerstand Einspeisung Ende (kWh)</td>
                            <td id="detail_grid_f_end" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Zählerstand Wechselrichter Anfang (kWh)</td>
                            <td id="detail_inv_total_start" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Zählerstand Wechselrichter Ende (kWh)</td>
                            <td id="detail_inv_total_end" class="text-end"></td>
                        </tr>                        
                        <tr>
                            <td>Höchster Netz-Bezug (W)</td>
                            <td id="detail_grid_pc_high" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Niedrigster Netz-Bezug (W)</td>
                            <td id="detail_grid_pc_low" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Durchschnittlicher Netz-Bezug (W)</td>
                            <td id="detail_grid_pc_av" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Höchste Netz-Einspeisung (W)</td>
                            <td id="detail_grid_pf_high" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Niedrigste Netz-Einspeisung (W)</td>
                            <td id="detail_grid_pf_low" class="text-end"></td>
                        </tr>

                        <tr>
                            <td>Durchschnittliche Netz-Einspeisung (W)</td>
                            <td id="detail_grid_pf_av" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Höchste Solar-Leistung (W)</td>
                            <td id="detail_inv_p_high" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Niedrigste Solar-Leistung (W)</td>
                            <td id="detail_inv_p_low" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Durchschnittliche Solar-Leistung (W)</td>
                            <td id="detail_inv_p_av" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Höchste Wechselrichter-Temperatur (°C)</td>
                            <td id="detail_inv_t_high" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Niedrigste Wechselrichter-Temperatur (°C)</td>
                            <td id="detail_inv_t_low" class="text-end"></td>
                        </tr>
                        <tr>
                            <td>Durchschn. Wechselrichter-Temperatur (°C)</td>
                            <td id="detail_inv_t_av" class="text-end"></td>
                        </tr>



                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
            </div>
        </div>
    </div>
</div>

<script>
    const detailsModalOptions = {};
    const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'), detailsModalOptions);





    const detailsModalBtns = document.querySelectorAll('.detailsModalBtn');
    detailsModalBtns.forEach((elm, i) => {
        elm.addEventListener('click', (e) => {
            e.preventDefault();


            for (var d in elm.dataset) {
                if (document.getElementById('detail_' + d)) {
                    if (d == 'grid_c_start' || d == 'grid_c_end' || d == 'grid_f_start' || d == 'grid_f_end') {
                        document.getElementById('detail_' + d).innerText = (elm.dataset[d] / 1000);
                    } else if (d == 'inv_total_start' || d == 'inv_total_end' || d == 'inv_t_high' || d == 'inv_t_low' || d == 'inv_t_av') {
                        document.getElementById('detail_' + d).innerText = (elm.dataset[d] / 100);
                    } else {
                        document.getElementById('detail_' + d).innerText = elm.dataset[d];
                    }
                }
            }


            detailsModal.show();

        })
    })
</script>