# ahoy-tasmota-solar-manager
A quick and dirty solution for measuring and optimizing your "Balkonkraftwerk"
Derzeit nur auf deutsch verfügbar.

![ahoy-tasmota-solar-manager_home](https://github.com/REIZFLUT/ahoy-tasmota-solar-manager/assets/15573287/5a1e459d-f69e-489a-a90c-a2f4d6c728f0)

# Was kann die Software?
- Den aktuellen Verbrauch und Einspeisung aus der Tasmota-API auslesen und speichern
- Die aktuelle Erzeugung deiner Balkonanlage aus der AHOY-DTU-API auslesen und speichern
- *Die Drosselung deines Hoymiles Wechselrichters dynamisch anpassen (alle 10 Sekunden)*
- Tägliche, wöchentliche, montatliche, jährliche Energiestatistik anzeigen
- Live-Monitoring des Verbrauchs und der Solar-Erzeugung
- Viele Einstellungen, um deinen Solarertrag zu optimieren


# Voraussetzungen
Die Software wurde speziell für die Zusammenarbeit zwischen einem "Volkszähler"
(Tasmota Smart Meter, https://tasmota.github.io/docs/Smart-Meter-Interface/) und 
einer AhoyDTU (https://ahoydtu.de/) entwickelt.

Die Software wurd absichtlich ohne PHP-Framework und Paketabhängigkeiten geschrieben, 
um sie schnell und einfach auf einer NAS oder einem Raspberry PI installieren zu können.

Du benötigst die entsprechende Hardware und Software fertig konfiguriert und angeschlossen, 
um den Manager verwenden zu können. Ausführliche Anleitungen findest du je auf den oben genannten Projektseiten.

# Installation grundsätzlich
- Daten herunterladen
- Auf einem Webserver ablegen
- Adresse der Ahoy-DTU und des Volkszählers unter (http://deine-url/settings.php) eingeben
- Den Worker-Service cron.php minütlich ausführen lassen
- Den Worker-Service cron_horuly.php stündlich ausführen lassen

Anweisungen für die Installation auf einer Synology NAS folgen.

# Weitere Screens

Monitor-Ansicht

![ahoy-tasmota-solar-manager_monitor](https://github.com/REIZFLUT/ahoy-tasmota-solar-manager/assets/15573287/a11f5ce1-861d-4f84-8634-6c8dfdfea924)

Einstellungen

![ahoy-tasmota-solar-manager_settings](https://github.com/REIZFLUT/ahoy-tasmota-solar-manager/assets/15573287/74d9d170-3dbd-4a59-a0fd-99c266ce6913)

