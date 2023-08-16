<?php 
require __DIR__ . '/database/sqlite.php';

use App\Database\Sqlite;

Sqlite::init(__DIR__ . '/database/database01.sqlite');

$timezone = Sqlite::selectById('system_config', 'timezone', '*', 'k')['v'];
$GLOBALS['CONFIG']['Timezone']                  = new DateTimeZone($timezone);
date_default_timezone_set($timezone);

$GLOBALS['CONFIG']['DeletePowerLogAfterDays']   = intval(Sqlite::selectById('system_config', 'delete_powerlog_after_days', '*', 'k')['v']);
$GLOBALS['CONFIG']['EnergyTariff']              = floatval(Sqlite::selectById('system_config', 'energy_tariff', '*', 'k')['v']);

$GLOBALS['CONFIG']['UseVirtualFeedbackCounter'] = intval(Sqlite::selectById('system_config', 'use_virtual_feedback_counter', '*', 'k')['v']);
$GLOBALS['CONFIG']['VirtualFeedbackCounter']    = intval(Sqlite::selectById('system_config', 'virtual_feedback_counter', '*', 'k')['v']);

$GLOBALS['CONFIG']['UseDtu']                    = Sqlite::selectById('system_config', 'use_dtu', '*', 'k')['v']; 
$GLOBALS['CONFIG']['InverterId']                = Sqlite::selectById('system_config', 'inverter_id', '*', 'k')['v']; 

// Reaction when ...
$GLOBALS['CONFIG']['MaxGridConsumtion']         = intval(Sqlite::selectById('system_config', 'max_grid_consumtion', '*', 'k')['v']);
$GLOBALS['CONFIG']['MaxGridFeedback']           = intval(Sqlite::selectById('system_config', 'max_grid_feedback', '*', 'k')['v']);


$GLOBALS['CONFIG']['SmartMeter']['Url']         = Sqlite::selectById('system_config', 'smartmeter.url', '*', 'k')['v'];
$GLOBALS['CONFIG']['SmartMeter']['TotalInKey']  = 'Total_in';
$GLOBALS['CONFIG']['SmartMeter']['TotalOutKey'] = 'Total_out';
$GLOBALS['CONFIG']['SmartMeter']['TimeKey']     = 'Time';
$GLOBALS['CONFIG']['SmartMeter']['PowerKey']    = 'Power_curr';


$GLOBALS['CONFIG']['AhoyDTU']['BaseUrl']        = Sqlite::selectById('system_config', 'ahoydtu.base_url', '*', 'k')['v']; 


$GLOBALS['CONFIG']['AhoyDTU']['InverterMax']    = intval(Sqlite::selectById('system_config', 'ahoydtu.inverter_max', '*', 'k')['v']);
$GLOBALS['CONFIG']['AhoyDTU']['OutputMax']      = intval(Sqlite::selectById('system_config', 'ahoydtu.output_max', '*', 'k')['v']);
$GLOBALS['CONFIG']['AhoyDTU']['OutputMin']      = intval(Sqlite::selectById('system_config', 'ahoydtu.output_min', '*', 'k')['v']); // do not set minimum lower than 20
$GLOBALS['CONFIG']['AhoyDTU']['ReactionFactor'] = intval(Sqlite::selectById('system_config', 'ahoydtu.reaction_factor', '*', 'k')['v']); // smooth power values


$GLOBALS['CONFIG']['OpenDTU']['User']           = Sqlite::selectById('system_config', 'opendtu.usr', '*', 'k')['v'];;
$GLOBALS['CONFIG']['OpenDTU']['Password']       = Sqlite::selectById('system_config', 'opendtu.pwd', '*', 'k')['v'];;