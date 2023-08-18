<?php

set_time_limit(58); // this way
ini_set('max_execution_time', 58);

require __DIR__ . '/app/config.php';
require __DIR__ . '/app/sensors/Sensor.php';
require __DIR__ . '/app/sensors/SmartMeter.php';
require __DIR__ . '/app/sensors/OpenDTU.php';
require __DIR__ . '/app/sensors/AhoyDTU.php';

use App\Database\Sqlite;
use App\Sensors\SmartMeter;
use App\Sensors\AhoyDTU;
use App\Sensors\OpenDTU;

// INIT
echo 'Gestartet: '.date('d.m.Y H:i:s')."\n";

$ts = time();

// RUN
for ($i = 0; $i < 6; $i++) {
    $smartMeter = SmartMeter::load($GLOBALS['CONFIG']['SmartMeter']['Url']);

    if($GLOBALS['CONFIG']['UseDtu'] == 'open'){
        $DTU    = OpenDTU::load($GLOBALS['CONFIG']['AhoyDTU']['BaseUrl']);
    } else {
        $DTU    = AhoyDTU::load($GLOBALS['CONFIG']['AhoyDTU']['BaseUrl']);
    }

    


    // CHECK IF AVAILABLE
    if ($smartMeter->connected && $DTU->connected) {

        if($GLOBALS['CONFIG']['UseVirtualFeedbackCounter']){
            $grid_counter_out = intval($GLOBALS['CONFIG']['VirtualFeedbackCounter']);
        } else {
            $grid_counter_out = intval(round($smartMeter->total_out * 1000));
        }

        // WRITE CONSUMTION AND PRODUCTION LOG
        $id = Sqlite::insert('power_log', [
            'measured_at' => time(),
            'power_grid' => intval(round($smartMeter->power)),
            'power_solar' => intval(round($DTU->power_ac)),
            'inverter_power_limit_is' => intval(round($DTU->active_power_limit)),
            'inverter_power_limit_adjust' => intval(round($DTU->active_power_limit)),
            'inverter_power_limit_state' => 0,
            'grid_counter_in' => intval(round($smartMeter->total_in * 1000)),
            'grid_counter_out' => $grid_counter_out,
            'inverter_temp' => intval(round($DTU->temp * 100)),
            'inverter_total' => intval(round($DTU->total * 100))
        ]);

        // approx current power
        $consumtion_grid  = ($smartMeter->power >= 0) ? $smartMeter->power : 0;
        $feedback_grid    = ($smartMeter->power < 0) ? $smartMeter->power * -1 : 0;
        $consumtion_solar = $DTU->power_ac - $feedback_grid;
        $consumtion_total = $DTU->power_ac + $consumtion_grid - $feedback_grid;


        // ADJUST POWER PRDUCTION IF NEEDED
        if (
            $consumtion_grid > $GLOBALS['CONFIG']['MaxGridConsumtion'] ||
            $feedback_grid > $GLOBALS['CONFIG']['MaxGridFeedback']
        ) {
            $result = $DTU->limitPower($consumtion_total, $GLOBALS['CONFIG']['AhoyDTU']['BaseUrl']);

            // UPDATE LOG
            Sqlite::updateById('power_log', $id, [
                'inverter_power_limit_adjust' => $result['active_power_limit_perc'],
                'inverter_power_limit_state' => $result['state_code']
            ]);
        }
    }
    
    

    if($i != 5) {
        $ts = time() + 10 - (time() - $ts);
        time_sleep_until($ts);
    }
}
echo 'Beendet: '.date('d.m.Y H:i:s')."\n";