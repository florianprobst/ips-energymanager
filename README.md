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

Eine weitere Aufgabe ist die ständige Überwachung von Geräten. Es ist möglich, beispielsweise die Waschmaschine oder den Trockner welche über eine Steckdose mit Stromverbrauchsmessung betrieben wird, so einzurichten, dass das Skript erkennt ob das Gerät läuft oder nicht. Es werden dann beispielsweise bei der Waschmaschine Push-Benachrichtigungen an mobile Clients gesendet, welche mitteilen ob das Gerät nun läuft oder nicht. (In meinem Fall ist das Praktisch, da ich nicht mehr in den Keller rennen muss um zu sehen ob die Waschmaschine noch läuft :-))

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

$configId = 45584 /*[System\IPS-EnergyManager\config]*/ ; //NICHT $_IPS['self'] benutzen, sondern ID dieses Scripts hier eintragen!
$parentId = 26332 /*[System\IPS-EnergyManager\Variables]*/; //Ablageort für erstellte Variablen
$webfrontId = 16219 /*[Webfront]*/;
$price_per_kwh = 0.2378; // Preis pro Kilowattstunde deines Stromanbieters
$debug = true;
$prefix = "EM_";  //prefix für den Namen der anzulegenden Variablen
$archive_id = 34760 /*[Archive]*/; //Instanz ID des IPS-Archivs in welchem die Werte des Stromzählers geloggt werden sollen.
$update_interval = 120; //Intervall in Sekunden in welchem die Geräte überwacht werden

//Ergänze alle IDs der zu überwachenden Stromzähler von Homematic (Typ HM_ES_PMSw1_PL) im nachfolgenden Array
$id_array_homematic_powermeters_HM_ES_PMSw1_PL = [
14379,
22517 /*[Hardware\Keller\Waschraum\Waschmaschine\POWERMETER]*/,
47796,
36598
];

/** DIESEN TEIL NICHT ÄNDERN! UNTEN GEHT'S WEITER **/
	$energymanager = new EnergyManager($configId, $webfrontId, $parentId, $archive_id, $price_per_kwh, $update_interval, $prefix, $debug);
	foreach($id_array_homematic_powermeters_HM_ES_PMSw1_PL as &$id){
		$energymanager->registerPowerMeter( new HomeMaticPowerMeterHM_ES_PMSw1_Pl($id) );
	}
/** AB HIER WIEDER ÄNDERN **/

/**Hier sind zu überwachende Geräte einzustellen.
D.h. es wird eine PUSH-Mitteilung auf dein Handy geschickt, sobald das Gerät im Standby oder ausgeschaltetem Zustand ist.
Und es erfolgt eine PUSH-Mitteilung wenn das Gerät "läuft". In diesem Beispiel ist eine Siemens Waschmaschine mit einer Standby-Schaltung von unter 4 Watt Verbrauch
und eine "läuft"-Schaltung ab 7 Watt eingestellt. Das Skript prüft mehrmals auf Unter-/Überschreiten der Schwellen im Tug-Of-War-Verfahren um Fehlmeldungen bei kurzzeitigem
Unter-/Überschreiten zu verhindern. Dadurch erfolgt die PUSH-Mitteilung zeitversetzt. (ca. 2 Minuten)

SYNTAX: $energymanager-> registerDevice(GERÄTENAME PUSH MITTEILUNG, INSTANZID DES POWERMETERS (WATTVERBRAUCH), STANDBYGRENZE IN WATT, EINGESCHALTETGRENZE IN WATT, GERÄTENAME, GERÄTETYP);
**/
$energymanager-> registerDevice("Waschmaschine", 22517 /*[Hardware\Keller\Waschraum\Waschmaschine\POWERMETER]*/, 4, 7, "Siemens", "IQ-800");
$energymanager-> registerDevice("Trockner", 14379 /*[Hardware\Keller\Waschraum\Trockner\POWERMETER]*/, 2, 10, "Bosch", "");
?>
```

##Notwendigkeit der Zählerstandvariablen
Die Zählerstände sind aus zwei Gründen separat in Skripteigenen Variablen gespeichert:
* Einheitliche Syntax: unabhängig vom Fabrikat werden immer die Zählerstände aller angebundenen Geräte in gleich lautenden Variablen und in dem gleichen Format / Einheit gespeichert.
* Wie im Fall des HomeMatic Zählers wird der Zählerstand des Gerätes bei einem Stromausfall auf 0 zurück gesetzt. Durch die separate Speicherung in eigenen Variablen kann das vermieden werden.

##Screenshots
![auswertung](assets/screenshot_v091_em_statistics.png)
![ips variables](assets/screenshot_v091_em_console_structure.png)
