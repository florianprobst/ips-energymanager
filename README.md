# ips-energymanager
IP-Symcon Addon zur Energie-Verbrauchsüberwachung und Dokumentation

[![Release](https://img.shields.io/github/release/florianprobst/ips-energymanager.svg?style=flat-square)](https://github.com/florianprobst/ips-energymanager/releases/latest)
[![License](https://img.shields.io/badge/license-LGPLv3-brightgreen.svg?style=flat-square)](https://github.com/florianprobst/ips-energymanager/blob/master/LICENSE)

## Aufgabe des Skripts
Dieses Addon dient dazu, in [IP-Symcon](http://www.symcon.de) alle Geräte mit einer Stromüberwachung/-Erfassung in geeigneter Weise auszuwerten.
Dazu gehören Daten wie: 

* aktuelle Zählerstände
* aktueller Verbrauch
* kumulierter Verbrauch
* Verbrauch je Abrechnungsperiode
* Stromkosten je Abrechnungsperiode
* Stromkosten aktuell je Tag / Monat

# Unterstützte Hardware
Folgende Geräte werden derzeit unterstützt
## HomeMatic
* HM-ES-PMSw1-Pl - Funk-Schaltaktor 1-fach mit Leistungsmessung

## Weiterführende Informationen
Das Skript legt selbstständig benötigte IPS-Variablen, Variablenprofile und Skripte an der im Konfigurationsskript festgelegten Stelle an.
Derzeit sind dies 2 Varibalen je Stromzähler, sowie eine globale Statistikvariable und 1 Variablenprofile. (Je nach IP-Symcon Lizenz bitte berücksichtigen)
Zur besseren Auffindbarkeit und eindeutigen Zuordnung werden alle Variablenprofile mit einem Präfix angelegt. 
Sofern in der Config nicht anderweitig angegeben lautet dieses standardmässig `EM_`.

## Installation

1. Dieses Repository im IP-Symcon Unterordner `webfront/user/` klonen. Bsp.: `C:\IP-Symcon\webfront\user\ips-energymanager` oder alternativ als zip-Datei herunterladen und in den `IP-Symcon/webfront/user` Unterordner entpacken.
2. In der IP-Symcon Verwaltungskonsole eine Kategorie `EnergyManager` und eine Unterkategorie `Variables` erstellen (Namen und Ablageorte sind frei wählbar)
3. Unterhalb der Kategorie `EnergyManager` ist das Config-Skript manuell anzulegen. Das anzulegendende Skript befinden sich im Unterordner `assets` und kann per copy&paste in die IPS-Console eingetragen werden. Alternativ ist das Skript auch weiter unten direkt beschrieben und kann von dort kopiert werden.

#### Struktur in der IP-Symcon Console nach Installation
(siehe dazu auch Screenshot unten)
* Speedport (Kategorie)
* - Variables (Kategorie)
* -- diverse automatisch generierte Statusvariablen nach erstem Statusupdate
* -- automatisch generierte Skripte und Events
* Config (script)

## IP-Symcon Console - anzulegende Skripte
###config script
Enthält die "globale" Konfiguration des EnergyManagers und wird von den anderen IPS-EnergyManager-Scripten aufgerufen.
Hier werden auch die Instanz-IDs aller zu überwachenden Stromzähler angegeben.

```php
<?
//Enthält die "globale" Konfiguration des EnergyManagers und wird von den anderen IPS-EnergyManager-Scripten aufgerufen.
//Hier werden auch die Instanz-IDs aller zu überwachenden Stromzähler angegeben.

require_once("../webfront/user/ips-energymanager/EnergyManager.class.php");

$parentId = 26332 /*[System\IPS-EnergyManager\Variables]*/; //Ablageort für erstellte Variablen
$price_per_kwh = 0.2378; // Preis pro Kilowattstunde deines Stromanbieters
$debug = true;
$prefix = "EM_";
$archive_id = 34760 /*[Archiv]*/; //Instanz ID des IPS-Archivs in welchem die Werte des Stromzählers geloggt werden sollen.
$update_interval = 120; //Intervall in Sekunden in welchem die Geräte überwacht werden

//Ergänze alle IDs der zu überwachenden Stromzähler von Homematic (Typ HM_ES_PMSw1_PL) im nachfolgenden Array
$id_array_homematic_powermeters_HM_ES_PMSw1_PL = [
29221,
21220,
31714
];

//ab hier nichts mehr ändern
$energymanager = new EnergyManager($parentId, $archive_id, $price_per_kwh, $update_interval, $prefix, $debug);

foreach($id_array_homematic_powermeters_HM_ES_PMSw1_PL as &$id){
	$energymanager->registerPowerMeter( new HomeMaticPowerMeterHM_ES_PMSw1_Pl($id) );
}
?>
```

##Notwendigkeit der Zählerstandvariablen
Die Zählerstände sind aus zwei Gründen separat in Skripteigenen Variablen gespeichert:
* Einheitliche Syntax: unabhängig vom Fabrikat werden immer die Zählerstände aller angebundenen Geräte in gleich lautenden Variablen und in dem gleichen Format / Einheit gespeichert.
* Wie im Fall des HomeMatic Zählers wird der Zählerstand des Gerätes bei einem Stromausfall auf 0 zurück gesetzt. Durch die separate Speicherung in eigenen Variablen kann das vermieden werden.

##Screenshots
![auswertung](assets/screenshot_v091_em_statistics.png)
![ips variables](assets/screenshot_v091_em_console_structure.png)