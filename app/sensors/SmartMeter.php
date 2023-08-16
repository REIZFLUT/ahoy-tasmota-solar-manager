<?php

namespace App\Sensors;

use App\Sensors\Sensor;
use DateTime;

class SmartMeter extends Sensor
{

    public $datetime;
    public $total_in;
    public $total_out;
    public $power;
    public $connected = false;

    public static function load($url)
    {
        $instance = new SmartMeter();

        $result = self::curl_get($url);
        
        if($result['info']['http_code'] == 200){
            $ra = json_decode($result['result'], true);

            $keys = array_keys($ra['StatusSNS']);
            $key = $keys[1];

            $tin_key   = $GLOBALS['CONFIG']['SmartMeter']['TotalInKey'];
            $tout_key  = $GLOBALS['CONFIG']['SmartMeter']['TotalOutKey'];
            $time_key  = $GLOBALS['CONFIG']['SmartMeter']['TimeKey'];
            $power_key = $GLOBALS['CONFIG']['SmartMeter']['PowerCurr'];

            $instance->datetime  = new DateTime($ra['StatusSNS'][$time_key], $GLOBALS['CONFIG']['Timezone']);
            $instance->total_in  = $ra['StatusSNS'][$key][$tin_key];
            $instance->total_out = isset($ra['StatusSNS'][$key][$tout_key]) ? $ra['StatusSNS'][$key][$tout_key]:0;
            $instance->power     = $ra['StatusSNS'][$key][$power_key];
            $instance->connected = true;
        }
        return $instance;
    }


}

