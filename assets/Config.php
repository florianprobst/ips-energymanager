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