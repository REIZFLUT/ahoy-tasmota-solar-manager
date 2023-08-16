<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header('Content-Type: text/plain');

require __DIR__ . '/app/config.php';
require __DIR__ . '/app/sensors/Sensor.php';
require __DIR__ . '/app/sensors/SmartMeter.php';
require __DIR__ . '/app/sensors/AhoyDTU.php';
require __DIR__ . '/app/sensors/OpenDTU.php';


use App\Database\Sqlite;
use App\Sensors\SmartMeter;
use App\Sensors\AhoyDTU;
use App\Sensors\OpenDTU;


echo 'Versuche Verbindungsaufbau zu: '.$GLOBALS['CONFIG']['AhoyDTU']['BaseUrl']. '/api/livedata/status'."\n\n";

$openDTU = new OpenDTU();
$result = OpenDTU::curl_get($GLOBALS['CONFIG']['AhoyDTU']['BaseUrl']. '/api/livedata/status');
if($result['info']['http_code'] == 200){
    
    echo 'Verbindung erfolgreich, Status 200.'."\n\n";

    $data = json_decode($result['result'], true);
    if($data){
        if(isset($data['avail_endpoints'])){
            echo 'Du hast dich mit der AhoyDTU verbunden, nicht mit der OpenDTU. Passe deine Einstellungen an.'."\n\n"; exit;
        }

        if(isset($data['inverters'])){
            echo 'Einstiegspunkt "inverters" gefunden.'."\n\n";

            $inv_found = false;

            foreach($data['inverters'] as $inv){
                if($inv['serial'] == $GLOBALS['CONFIG']['InverterId']){
                    $inv_found = true;
                    echo 'Inverter mit der Seriennummer (serial) "'.$GLOBALS['CONFIG']['InverterId'].'" gefunden.'."\n\n";
                    echo 'Relevante Live Daten:'."\n\n";
                    echo 'Relatives Limit: '. $inv['limit_relative']."\n";
                    echo 'Aktueller Output AC: '.$inv['AC'][0]['Power']['v']."\n";
                    echo 'Aktuelle Temperatur: '.$inv['INV'][0]['Temperature']['v']."\n";
                    echo 'Zählerstand Inverter (YieldTotal): '. $inv['AC'][0]['YieldTotal']['v']."\n";
                    echo 'Inverter ist erreichbar: '.($inv['reachable'] ? 'ja':'nein')."\n";         
                    exit;           
                }
            }

            if(!$inv_found){
                echo 'Inverter mit der Seriennummer (serial) "'.$GLOBALS['CONFIG']['InverterId'].'" nicht gefunden. Passe deine Einstellungen an.'; exit;
            }

        } else {
            echo 'Einstiegspunkt "inverters" nicht gefunden.'; exit;
        }

    } else {
        echo 'Kein valides JSON. Wahrscheinlich falsche URL.'."\n\n";
    }
} else {
    echo 'Falsche URL oder DTU oder Route nicht erreichbar.';
}

//var_dump($openDTU);
