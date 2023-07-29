<?php

use App\Database\Sqlite;

require __DIR__ . '/app/config.php';

if (isset($_GET['date'])) {
    list($year, $month, $day) = explode('-', $_GET['date']);
} else {
    $year = date('Y');
    $month = date('n');
    $day = date('j');
}

if(count($_POST) > 0){

    if(isset($_POST['timezone'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['timezone'], 'timezone']);
    }

    if(isset($_POST['delete_powerlog_after_days'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [intval($_POST['delete_powerlog_after_days']), 'delete_powerlog_after_days']);
    }

    if(isset($_POST['smartmeter_url'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['smartmeter_url'], 'smartmeter.url']);
    }  

    if(isset($_POST['ahoydtu_base_url'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['ahoydtu_base_url'], 'ahoydtu.base_url']);
    }    

    if(isset($_POST['ahoydtu_inverter_max'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['ahoydtu_inverter_max'], 'ahoydtu.inverter_max']);
    } 

    if(isset($_POST['ahoydtu_output_max'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['ahoydtu_output_max'], 'ahoydtu.output_max']);
    }   
    
    if(isset($_POST['ahoydtu_output_min'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['ahoydtu_output_min'], 'ahoydtu.output_min']);
    }      
    
    if(isset($_POST['max_grid_consumtion'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['max_grid_consumtion'], 'max_grid_consumtion']);
    }    
    
    if(isset($_POST['max_grid_feedback'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['max_grid_feedback'], 'max_grid_feedback']);
    }         

    if(isset($_POST['ahoydtu_reaction_factor'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['ahoydtu_reaction_factor'], 'ahoydtu.reaction_factor']);
    }     
    
    if(isset($_POST['energy_tariff'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['energy_tariff'], 'energy_tariff']);
    }   

    if(isset($_POST['use_virtual_feedback_counter'])){
        Sqlite::query('UPDATE system_config SET v = ? WHERE k = ?', [$_POST['use_virtual_feedback_counter'], 'use_virtual_feedback_counter']);
    }     

    

    header('Location: settings.php');
}

?><!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Einstellungen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
    <?php $page = 'settings'; include __DIR__ . '/app/incl/nav.php'; ?>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                Einstellungen
            </div>
            <div class="card-body">
                <form method="post">
                    <h4>Basis Infos</h4>
                    <div class="mb-3">
                        <label class="form-label">Zeitzone</label>
                        <input type="text" name="timezone" value="<?= $timezone; ?>" class="form-control" required>
                        <small>Liste valider Zeitzohnen unter <a href="https://www.php.net/manual/de/timezones.php" target="_blank">https://www.php.net/manual/de/timezones.php</a></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monitor-Daten nach X Tagen löschen</label>
                        <input type="number" name="delete_powerlog_after_days" value="<?= $GLOBALS['CONFIG']['DeletePowerLogAfterDays'] ; ?>" class="form-control" required min="1" step="1">
                        <small>Pro Tag werden etwa 8640 Datenpunkte erfasst. Längere Speicherzeiten erhöhen die Menge an benötigtem Speicherplatz für die Datenbank und sorgen ggf. für Performace-Einbußen. Empfohlene Löschzeit: 7 Tage.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stromkosten pro kWh in Euro</label>
                        <input type="number" name="energy_tariff" value="<?= $GLOBALS['CONFIG']['EnergyTariff'] ; ?>" class="form-control" required min="0.01" step="0.01">
                        
                    </div>
                    
                    <hr>     
                    <h4>Smart Meter</h4>               
                    <div class="mb-3">
                        <label class="form-label">Smart-Meter-Url (API)</label>
                        <input type="url" name="smartmeter_url" value="<?= $GLOBALS['CONFIG']['SmartMeter']['Url']  ; ?>" class="form-control" required>
                        <small>Beispiel: http://mein-volkszaehler/cm?cmnd=status%208</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Smart-Meter ohne Einspeise-Zähler (Virtuellen Einspeise-Zähler verwenden)</label>
                        <input type="number" name="use_virtual_feedback_counter" value="<?= $GLOBALS['CONFIG']['UseVirtualFeedbackCounter']  ; ?>" class="form-control" min="0" max="1" step="1" required>
                        <small>Wenn dein Zähler keine Position für "Total_out" hat, kannst du den virtuellen Zähler verwenden. Er errechnet dann aus der durchschnittlichen Rückspeise-Leistung in Watt, deinen Verbrauch in kWh.
                            Im Monitor wird in der Wertetabelle der Zählerwert nur stündlich aktualisiert. (1 = Virtueller Zähler an; 0 = Virtueller Zähler aus).<br>
                            Aktueller Virtueller Einspeise-Zähler-Wert: <?= round($GLOBALS['CONFIG']['VirtualFeedbackCounter'] / 1000, 2); ?> kWh
                        </small>
                    </div>
                                        
                    <hr>
                    <h4>Ahoy DTU</h4>               
                    <div class="mb-3">
                        <label class="form-label">Ahoy DTU Basis-Url</label>
                        <input type="url" name="ahoydtu_base_url" value="<?= $GLOBALS['CONFIG']['AhoyDTU']['BaseUrl']  ; ?>" class="form-control" required>
                        <small>Beispiel: http://ahoy-dtu</small>
                    </div>   
                    <hr>
                    <h4>Dynamische Drosselung</h4>
                    <div class="alert alert-info">Durch die Einstellung einer dynamischen Drosselung, wird die Leistungsfähigkeit Ihrer Anlage auf die von Ihnen eingestellten Werte begrenzt. 
                                     Es wird nicht berücksichtigt, was die Anlage leisten KÖNNTE.</div> 
                    <div class="alert alert-warning">
                        <b>Bitte halten Sie sich an die Normen und Vorschriften in Ihrer Region. Für Rechtsfolgen aus fehlerhafter Konfiguration sind Sie selbst verantwortlich.</b>
                    </div>
                    <div class="alert alert-danger">
                        <b>Durch häufige Steuerimpulse, kann Ihre Anlage schneller verschleißen.</b>
                    </div>                    
                    <p>
                       Die dynamische Drosselung erkennt, wie viel Leistung (W) im Moment benötigt wird (Netzbezug + Solar - Einspeisung). 
                       Ist die Leistung aus dem Netz höher als der Schwellwert für den Bezug, wird nachgeregelt und versucht mehr Leistung aus der Solaranlage zur Verfügung zu stellen.
                       Ist die Rückspeisung ins Netz höher als der Schwellwert für die Rückspeisung, wird nachgeregelt und versucht weniger Leistung zur Verfügung zu stellen.
                       Das Spektrum der möglichen Leistungsabgaben wird durch den eingestellten Maximal- und Minimalwert eingegrenzt. Haben beide den gleichen Wert, wird die dynamische Drosselung zu einer statischen Drosselung.
                       Der Glättungsfaktor hilft dabei, die Anzahl der Steuerimpulse zu minimieren und damit die Elektronik zu schonen.
                       
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Wechselrichter Maximalleistung in Watt</label>
                        <input type="number" name="ahoydtu_inverter_max" value="<?= $GLOBALS['CONFIG']['AhoyDTU']['InverterMax']  ; ?>" min="100" step="1" class="form-control" required>
                        <small>Gemäß Typenschild. Beispiel HM-1500 kann 1500 W: Zahl "1500" eingeben</small>
                    </div>     
                    <div class="mb-3">
                        <label class="form-label">Dynamische Drosselung Maximalwert (Watt)</label>
                        <input type="number" name="ahoydtu_output_max" value="<?= $GLOBALS['CONFIG']['AhoyDTU']['OutputMax']  ; ?>" min="100" step="1" class="form-control" required>
                        <small>Z.B. 600; Der Wechselrichter gibt MAXIMAL diese Anzahl an Watt aus, auch wenn Verbraucher mehr ziehen möchten bzw. die Anlage mehr leisten könnte.
                            Der Maximalwert darf nicht größer sein, als die Maximalleistung des Wechselrichters und die gesetzlich zulässige Einspeisung ins Hausnetz.
                            Ein höherer Maximalwert nutzt die Anlage besser aus, wenn zusätzliche Verbraucher Leistung benötigen. 
                        </small>
                    </div>    
                    <div class="mb-3">
                        <label class="form-label">Dynamische Drosselung Minimalwert (Watt)</label>
                        <input type="number" name="ahoydtu_output_min" value="<?= $GLOBALS['CONFIG']['AhoyDTU']['OutputMin']  ; ?>" min="100" step="1" class="form-control" required>
                        <small>Z.B. 200; Der Wechselrichter gibt MINDESTENS diese Anzahl an Watt aus, auch wenn Verbraucher weniger ziehen möchten. Der Überschuss wird ins Netz abgegeben. 
                               Ein höherer Minimalwert, speist ggf. dauerhaft mehr ins Netz zurück, hilft jedoch auch kleinere Leistungsspitzen noch aus der Anlage beziehen zu können,
                               bis das System nachgeregelt hat. Empfehlung: Grundlast + 50%</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Schwellwert Bezug (Watt)</label>
                        <input type="number" name="max_grid_consumtion" value="<?= $GLOBALS['CONFIG']['MaxGridConsumtion']; ?>" min="1" step="1" class="form-control" required>
                        <small>Z.B. 50; Berechne einen neuen Drosselungswert, wenn der Schwellwert im Bezug überschritten wird.</small>
                        
                    </div>  
                    <div class="mb-3">
                        <label class="form-label">Schwellwert Rückspeisung (Watt)</label>
                        <input type="number" name="max_grid_feedback" value="<?= $GLOBALS['CONFIG']['MaxGridFeedback'] ; ?>" min="1" step="1" class="form-control" required>
                        <small>Z.B. 200; Berechne einen neuen Drosselungswert, wenn der Schwellwert in der Rückspeisung überschritten wird.</small>
                        
                    </div>                                        
                    <div class="mb-5">
                        <label class="form-label">Glättungsfaktor</label>
                        <input type="number" name="ahoydtu_reaction_factor" value="<?= $GLOBALS['CONFIG']['AhoyDTU']['ReactionFactor']  ; ?>" min="1" step="1" class="form-control" required>
                        <small>Empfehlung G = 50;<br>
                            
                            Der Glättungsfaktor steuert die Tolleranz der dynamischen Drosselung auf Grundlage der benötigten Gesamtleistung (Netzbezug + Solar - Einspeisung). <br>
                            Ein geringerer Wert erkennt kleinere Schwankungen und löst schneller einen Steuerimpuls aus. Die Anpassung wird feiner. Ein höherer Wert reagiert träger und gröber.<br>
                            
                            Beispiel Glättungsfaktor 1: Der Unterschied zwischen 600 W und 601 W benötigter Gesamtleistung wird erkannt. <br>
                            Beispiel Glättungsfaktor 50: Werte zwischen 575 W und 625 W werden als 600 W benötigte Gesamtleistung angenommen. <br>
                            Es werden also maximal 25 W aus dem Netz bezogen oder eingespeist, wenn sich der Wert bis zur nächsten Messung nicht stark ändert.<br>
                            Der Steuerimpuls wird erst bei größeren Unterschieden ausgelöst. Dies führt zwischen zwei Messungen ggf. zu höheren Bezügen oder Rückspeisungen, verringert aber die Anzahl der benötigten Steuerimpulse.<br>
                            <br>
                            Formel: W<sub>glatt</sub> = [W<sub>IST</sub> / G ≈ W<sub>DIV</sub>] * G); <br>
                            Beispiel: W<sub>glatt</sub> = [583 W / 50 ≈ 12] * 50 = 600 W; 
                            
                        </small>
                    </div>  
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>                                                                                            
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</body>

</html>