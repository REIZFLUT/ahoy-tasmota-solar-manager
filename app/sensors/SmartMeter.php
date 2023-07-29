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

            $instance->datetime  = new DateTime($ra['StatusSNS']['Time'], $GLOBALS['CONFIG']['Timezone']);
            $instance->total_in  = $ra['StatusSNS'][$key]['Total_in'];
            $instance->total_out = isset($ra['StatusSNS'][$key]['Total_out']) ? $ra['StatusSNS'][$key]['Total_out']:0;
            $instance->power     = $ra['StatusSNS'][$key]['Power_curr'];
            $instance->connected = true;
        }
        return $instance;
    }


}

