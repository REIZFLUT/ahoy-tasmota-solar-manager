<?php

require __DIR__ . '/../app/database/sqlite.php';

use App\Database\Sqlite;

Sqlite::init(__DIR__ . '/../app/database/database01.sqlite');

echo '<pre>';
if(Sqlite::selectById('system_config', 'timezone', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'timezone', 'v' => 'Europe/Amsterdam']);
    echo "Created timezone setting\n";
}

if(Sqlite::selectById('system_config', 'delete_powerlog_after_days', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'delete_powerlog_after_days', 'v' => '7']);
    echo "Created delete_powerlog_after_days setting\n";
}

if(Sqlite::selectById('system_config', 'energy_tariff', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'energy_tariff', 'v' => '0.3']);
    echo "Created energy_tariff setting\n";
}

if(Sqlite::selectById('system_config', 'use_virtual_feedback_counter', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'use_virtual_feedback_counter', 'v' => '0']);
    echo "Created use_virtual_feedback_counter setting\n";
}

if(Sqlite::selectById('system_config', 'virtual_feedback_counter', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'virtual_feedback_counter', 'v' => '0']);
    echo "Created virtual_feedback_counter setting\n";
}

if(Sqlite::selectById('system_config', 'max_grid_consumtion', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'max_grid_consumtion', 'v' => '50']);
    echo "Created max_grid_consumtion setting\n";
}

if(Sqlite::selectById('system_config', 'max_grid_feedback', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'max_grid_feedback', 'v' => '200']);
    echo "Created max_grid_feedback setting\n";
}

if(Sqlite::selectById('system_config', 'use_dtu', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'use_dtu', 'v' => 'ahoy']);
    echo "Created use_dtu setting\n";
}

if(Sqlite::selectById('system_config', 'inverter_id', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'inverter_id', 'v' => '0']);
    echo "Created inverter_id\n";
}

if(Sqlite::selectById('system_config', 'smartmeter.url', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'smartmeter.url', 'v' => 'http://192.168.178.123/cm?cmnd=status%208']);
    echo "Created smartmeter.url setting\n";
}

if(Sqlite::selectById('system_config', 'ahoydtu.base_url', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'ahoydtu.base_url', 'v' => 'http://ahoy-dtu']);
    echo "Created ahoydtu.base_url setting\n";
}

if(Sqlite::selectById('system_config', 'ahoydtu.inverter_max', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'ahoydtu.inverter_max', 'v' => '1500']);
    echo "Created ahoydtu.inverter_max setting\n";
}

if(Sqlite::selectById('system_config', 'ahoydtu.output_max', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'ahoydtu.output_max', 'v' => '600']);
    echo "Created ahoydtu.output_max setting\n";
}

if(Sqlite::selectById('system_config', 'ahoydtu.output_min', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'ahoydtu.output_min', 'v' => '450']);
    echo "Created ahoydtu.output_min setting\n";
}

if(Sqlite::selectById('system_config', 'ahoydtu.reaction_factor', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'ahoydtu.reaction_factor', 'v' => '50']);
    echo "Created ahoydtu.reaction_factor setting\n";
}

if(Sqlite::selectById('system_config', 'opendtu.usr', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'opendtu.usr', 'v' => '']);
    echo "Created opendtu.user setting\n";
}

if(Sqlite::selectById('system_config', 'opendtu.pwd', '*', 'k') == false){
    Sqlite::insert('system_config', ['k' => 'opendtu.pwd', 'v' => '']);
    echo "Created opendtu.pwd setting\n";
}

echo '</pre>';