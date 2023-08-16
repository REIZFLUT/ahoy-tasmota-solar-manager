<?php

namespace App\Sensors;

use App\Sensors\Sensor;

class OpenDTU extends Sensor
{

    public $active_power_limit;
    public $power_ac;
    public $temp;
    public $total;

    public $connected = false;

    public static function load($base_url)
    {

        $instance = new OpenDTU();
        $result = self::curl_get($base_url . '/api/livedata/status');
        

        if ($result['info']['http_code'] == 200) {
            $ra = json_decode($result['result'], true);
            foreach($ra['inverters'] as $inverter){
                if($inverter['serial'] == $GLOBALS['CONFIG']['InverterId']){
                    $instance->active_power_limit = $inverter['limit_relative'];
                    $instance->power_ac = $inverter['reachable'] ? $inverter['AC'][0]['Power']['v']:0;
                    $instance->temp     = $inverter['reachable'] ? $inverter['INV'][0]['Temperature']['v']:0;
                    $instance->total    = $inverter['AC'][0]['YieldTotal']['v'];
                    $instance->connected = true;
                }
            }      
        } else {
            $instance->active_power_limit = 0;
            $instance->power_ac = 0;
            $instance->temp     = 0;
            $instance->total    = 0;            
            $instance->connected = false;
        }

        return $instance;

    }

    public function limitPower($demand, $base_url)
    {

        // static power limit
        if ($GLOBALS['CONFIG']['AhoyDTU']['OutputMax'] == $GLOBALS['CONFIG']['AhoyDTU']['OutputMin']) {
            $power_limit_perc = intval(round($GLOBALS['CONFIG']['AhoyDTU']['OutputMax'] / $GLOBALS['CONFIG']['AhoyDTU']['InverterMax'] * 100, 2));

            if ($power_limit_perc != $this->active_power_limit) {
                return $this->sendPowerLimit($power_limit_perc, $base_url);
            } else {
                return [
                    'active_power_limit_perc' => $this->active_power_limit,
                    'success' => true,
                    'message' => 'Nothing changed.',
                    'state_code' => 0
                ];
            }
            // dynamic power limit
        } else {
            $dem_flat = intval(round($demand / $GLOBALS['CONFIG']['AhoyDTU']['ReactionFactor']) * $GLOBALS['CONFIG']['AhoyDTU']['ReactionFactor']);
            $cmx = $GLOBALS['CONFIG']['AhoyDTU']['InverterMax'] * $this->active_power_limit / 100;
            $cmx_flat = intval(round($cmx / $GLOBALS['CONFIG']['AhoyDTU']['ReactionFactor']) * $GLOBALS['CONFIG']['AhoyDTU']['ReactionFactor']);

            if ($dem_flat > $GLOBALS['CONFIG']['AhoyDTU']['OutputMax']) {
                $output = $GLOBALS['CONFIG']['AhoyDTU']['OutputMax'];
            } elseif ($dem_flat < $GLOBALS['CONFIG']['AhoyDTU']['OutputMin']) {
                $output = $GLOBALS['CONFIG']['AhoyDTU']['OutputMin'];
            } else {
                $output = $dem_flat;
            }

            if ($cmx_flat != $output) {
                $power_limit_perc = intval(round($output / $GLOBALS['CONFIG']['AhoyDTU']['InverterMax'] * 100));
                return $this->sendPowerLimit($power_limit_perc, $base_url);
            } else {
                return [
                    'active_power_limit_perc' => $this->active_power_limit,
                    'success' => true,
                    'message' => 'Nothing changed.',
                    'state_code' => 0
                ];
            }
        }
    }


    private function sendPowerLimit($power_limit_perc, $base_url)
    {
        $curl = curl_init();
        $inverter_id = intval($GLOBALS['CONFIG']['InverterId']);

        curl_setopt_array($curl, [
            CURLOPT_URL => $base_url . '/api/limit/config',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => trim($GLOBALS['CONFIG']['OpenDTU']['User']).':'.trim($GLOBALS['CONFIG']['OpenDTU']['Password']),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "serial":"'.$inverter_id.'",
            "limit_type": 1,
            "limit_value": "' . $power_limit_perc . '"
        }',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($info['http_code'] == 200) {
            $result = json_decode($response, true);
            if ($result['type'] == 'success') {
                return [
                    'active_power_limit_perc' => $power_limit_perc,
                    'success' => true,
                    'message' => 'Power limit set successfully.',
                    'state_code' => 1
                ];
            } else {
                return [
                    'active_power_limit_perc' => $power_limit_perc,
                    'success' => false,
                    'message' => 'Inverter offline.',
                    'state_code' => 2
                ];
            }
        } else {
            return [
                'active_power_limit_perc' => $power_limit_perc,
                'success' => false,
                'message' => 'DTU offline.',
                'state_code' => 3
            ];
        }
    }
}
