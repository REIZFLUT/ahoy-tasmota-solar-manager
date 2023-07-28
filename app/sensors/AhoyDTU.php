<?php

namespace App\Sensors;

use App\Sensors\Sensor;

class AhoyDTU extends Sensor
{

    public $active_power_limit;
    public $power_ac;
    public $temp;
    public $total;

    public $connected = false;

    public static function load($base_url)
    {

        $instance = new AhoyDTU();
        $connected_pl = false;
        $connected_lv = false;

        $result_pl = self::curl_get($base_url . '/api/record/config');
        if ($result_pl['info']['http_code'] == 200) {
            $ra = json_decode($result_pl['result'], true);
            $instance->active_power_limit = floatval($ra['inverter'][0][0]['val']);
            $connected_pl = true;
        }

        $result_lv = self::curl_get($base_url . '/api/record/live');
        $instance->total = 0;

        if ($result_pl['info']['http_code'] == 200) {
            $ra = json_decode($result_lv['result'], true);
            foreach ($ra['inverter'][0] as $r) {
                if ($r['fld'] == 'P_AC') {
                    $instance->power_ac = floatval($r['val']);
                }
                if ($r['fld'] == 'Temp') {
                    $instance->temp = floatval($r['val']);
                }
                if ($r['fld'] == 'YieldTotal') {
                    $instance->total = ($instance->total < floatval($r['val'])) ? floatval($r['val']) : $instance->total;
                }
            }
            $connected_lv = true;
        }

        $instance->connected = ($connected_pl && $connected_lv);

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

        curl_setopt_array($curl, [
            CURLOPT_URL => $base_url . '/api/ctrl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "id":"0",
            "cmd": "limit_nonpersistent_relative",
            "val": "' . $power_limit_perc . '"
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
            if ($result['success']) {
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
