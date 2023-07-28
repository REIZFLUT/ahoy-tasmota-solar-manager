<?php

set_time_limit(300); // this way
ini_set('max_execution_time', 300);

require __DIR__ . '/app/config.php';

use App\Database\Sqlite;

// start ts for statistics
$lsts_r = Sqlite::selectById('system_config', 'last_statistic_ts', 'v', 'k');
$ts_start = intval($lsts_r['v']);

// stop ts for statistics
$dt_stop_base = strtotime('last hour');
$ts_stop = mktime(intval(date('G', $dt_stop_base)), 59, 59, intval(date('n', $dt_stop_base)), intval(date('j', $dt_stop_base)), intval(date('Y', $dt_stop_base)));

$logs = Sqlite::query('SELECT * FROM power_log WHERE measured_at > ? AND measured_at <= ?', [$ts_start, $ts_stop]);

$hs = [];

foreach ($logs as $log) {
    $hs_id = date('Ymd-H', $log['measured_at']);

    if (!isset($hs[$hs_id])) {
        $hs[$hs_id] = [];
        $hs[$hs_id]['year']             = intval(date('Y', $log['measured_at']));
        $hs[$hs_id]['month']            = intval(date('n', $log['measured_at']));
        $hs[$hs_id]['day']              = intval(date('j', $log['measured_at']));
        $hs[$hs_id]['hour']             = intval(date('G', $log['measured_at']));
        $hs[$hs_id]['grid_c_start']     = intval($log['grid_counter_in']);
        $hs[$hs_id]['grid_c_end']       = intval($log['grid_counter_in']);
        $hs[$hs_id]['grid_f_start']     = intval($log['grid_counter_out']);
        $hs[$hs_id]['grid_f_end']       = intval($log['grid_counter_out']);
        $hs[$hs_id]['grid_pc_high']     = $log['power_grid'] > 0 ? $log['power_grid'] : 0;
        $hs[$hs_id]['grid_pc_low']      = $log['power_grid'] > 0 ? $log['power_grid'] : 0;
        $hs[$hs_id]['grid_pf_high']     = $log['power_grid'] < 0 ? $log['power_grid'] * -1 : 0;
        $hs[$hs_id]['grid_pf_low']      = $log['power_grid'] < 0 ? $log['power_grid'] * -1 : 0;
        $hs[$hs_id]['grid_pc_av']       = $log['power_grid'] > 0 ? $log['power_grid'] : 0;
        $hs[$hs_id]['grid_pf_av']       = $log['power_grid'] < 0 ? $log['power_grid'] * -1 : 0;
        $hs[$hs_id]['inv_p_high']       = $log['power_solar'];
        $hs[$hs_id]['inv_p_low']        = $log['power_solar'];
        $hs[$hs_id]['inv_p_av']         = $log['power_solar'];
        $hs[$hs_id]['inv_t_high']       = $log['inverter_temp'];
        $hs[$hs_id]['inv_t_low']        = $log['inverter_temp'];
        $hs[$hs_id]['inv_t_av']         = $log['inverter_temp'];
        $hs[$hs_id]['inv_total_start']  = $log['inverter_total'];
        $hs[$hs_id]['inv_total_end']    = $log['inverter_total'];

        $hs[$hs_id]['measure_cnt']      = 1;

        $hs[$hs_id]['sum_p_grid']       = $log['power_grid'] > 0 ? $log['power_grid'] : 0;
        $hs[$hs_id]['sum_f_grid']       = $log['power_grid'] < 0 ? $log['power_grid'] * -1 : 0;;
        $hs[$hs_id]['sum_p_inv']        = $log['power_solar'];
        $hs[$hs_id]['sum_t_inv']        = $log['inverter_temp'];
    } else {

        $hs[$hs_id]['grid_c_start']     = ($hs[$hs_id]['grid_c_start'] == 0) ? intval($log['grid_counter_in']):$hs[$hs_id]['grid_c_start'];

        $hs[$hs_id]['grid_c_end']       = $log['grid_counter_in'];
        $hs[$hs_id]['grid_f_end']       = $log['grid_counter_out'];
        $hs[$hs_id]['inv_total_end']    = $log['inverter_total'];


        if ($log['power_grid'] >= 0) {
            $hs[$hs_id]['grid_pc_high']     = $log['power_grid'] > $hs[$hs_id]['grid_pc_high'] ? $log['power_grid'] : $hs[$hs_id]['grid_pc_high'];
            $hs[$hs_id]['grid_pc_low']      = $log['power_grid'] < $hs[$hs_id]['grid_pc_low'] ? $log['power_grid'] : $hs[$hs_id]['grid_pc_low'];
            $hs[$hs_id]['grid_pf_low']      = 0;
            $hs[$hs_id]['sum_p_grid']      += $log['power_grid'];
        } else {
            $hs[$hs_id]['grid_pc_low']      = 0;
            $pf = $log['power_grid'] * -1;
            $hs[$hs_id]['grid_pf_high']     = $pf > $hs[$hs_id]['grid_pf_high'] ? $pf : $hs[$hs_id]['grid_pf_high'];
            $hs[$hs_id]['grid_pf_low']      = $pf < $hs[$hs_id]['grid_pf_low'] ? $pf : $hs[$hs_id]['grid_pf_low'];
            $hs[$hs_id]['sum_f_grid']      += $log['power_grid'] * -1;
        }

        $hs[$hs_id]['inv_p_high']           = $log['power_solar'] > $hs[$hs_id]['inv_p_high'] ? $log['power_solar'] : $hs[$hs_id]['inv_p_high'];
        $hs[$hs_id]['inv_p_low']            = $log['power_solar'] < $hs[$hs_id]['inv_p_low'] ? $log['power_solar'] : $hs[$hs_id]['inv_p_low'];
        $hs[$hs_id]['sum_p_inv']           += $log['power_solar'];

        $hs[$hs_id]['inv_t_high']           = $log['inverter_temp'] > $hs[$hs_id]['inv_t_high'] ? $log['inverter_temp'] : $hs[$hs_id]['inv_t_high'];
        $hs[$hs_id]['inv_t_low']            = $log['inverter_temp'] < $hs[$hs_id]['inv_t_low']  ? $log['inverter_temp'] : $hs[$hs_id]['inv_t_low'];
        $hs[$hs_id]['sum_t_inv']           += $log['inverter_temp'];

        $hs[$hs_id]['measure_cnt']++;

        $hs[$hs_id]['grid_pc_av'] = intval(round($hs[$hs_id]['sum_p_grid'] / $hs[$hs_id]['measure_cnt']));
        $hs[$hs_id]['grid_pf_av'] = intval(round($hs[$hs_id]['sum_f_grid'] / $hs[$hs_id]['measure_cnt']));
        $hs[$hs_id]['inv_p_av']   = intval(round($hs[$hs_id]['sum_p_inv']  / $hs[$hs_id]['measure_cnt']));
        $hs[$hs_id]['inv_t_av']   = intval(round($hs[$hs_id]['sum_t_inv']  / $hs[$hs_id]['measure_cnt']));
    }
}

foreach ($hs as $h_key => $h_val) {
    unset($h_val['sum_p_grid']);
    unset($h_val['sum_f_grid']);
    unset($h_val['sum_p_inv']);
    unset($h_val['sum_t_inv']);

    $prep = [$h_val['year'], $h_val['month'], $h_val['day'], $h_val['hour']];
    $existing = Sqlite::query('SELECT * FROM power_stat_hourly WHERE year = ? AND month = ? AND day = ? AND hour = ?', $prep);
    if(count($existing) == 0){
        Sqlite::insert('power_stat_hourly', $h_val);
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [mktime($h_val['hour'], 59, 59, $h_val['month'], $h_val['day'], $h_val['year']), 'last_statistic_ts']);
    }

    
}

Sqlite::query('DELETE FROM power_log WHERE measured_at < ?', [strtotime('today 00:00:00 -'.$GLOBALS['CONFIG']['DeletePowerLogAfterDays'].' days')]);

?>