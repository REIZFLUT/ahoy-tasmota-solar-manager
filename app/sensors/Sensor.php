<?php

namespace App\Sensors;


class Sensor
{

    public static function curl_get($url)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        $errno = curl_errno($curl);
        $errmg = curl_error($curl);

        curl_close($curl);

        return [
            'info' => $info,
            'result' => $result,
            'errno' => $errno,
            'errmg' => $errmg
        ];
    }


}
