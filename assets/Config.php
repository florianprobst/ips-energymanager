<?
//Enth�lt die "globale" Konfiguration des EnergyManagers und wird von den anderen IPS-EnergyManager-Scripten aufgerufen.
//Hier werden auch die Instanz-IDs aller zu �berwachenden Stromz�hler angegeben.

require_once("../webfront/user/ips-energymanager/EnergyManager.class.php");

$configId = 45584 /*[System\IPS-EnergyManager\config]*/ ; //NICHT $_IPS['self'] benutzen, sondern ID dieses Scripts hier eintragen!
$parentId = 26332 /*[System\IPS-EnergyManager\Variables]*/; //Ablageort f�r erstellte Variablen
$webfrontId = 16219 /*[Webfront]*/;
$price_per_kwh = 0.2378; // Preis pro Kilowattstunde deines Stromanbieters
$debug = true;
$prefix = "EM_";  //prefix f�r den Namen der anzulegenden Variablen
$archive_id = 34760 /*[Archive]*/; //Instanz ID des IPS-Archivs in welchem die Werte des Stromz�hlers geloggt werden sollen.
$update_interval = 120; //Intervall in Sekunden in welchem die Ger�te �berwacht werden

//Erg�nze alle IDs der zu �berwachenden Stromz�hler von Homematic (Typ HM_ES_PMSw1_PL) im nachfolgenden Array
$id_array_homematic_powermeters_HM_ES_PMSw1_PL = [
14379,
22517 /*[Hardware\Keller\Waschraum\Waschmaschine\POWERMETER]*/,
47796,
36598
];

/** DIESEN TEIL NICHT �NDERN! UNTEN GEHT'S WEITER **/
    $energymanager = new EnergyManager($configId, $webfrontId, $parentId, $archive_id, $price_per_kwh, $update_interval, $prefix, $debug);
    foreach($id_array_homematic_powermeters_HM_ES_PMSw1_PL as &$id){
        $energymanager->registerPowerMeter( new HomeMaticPowerMeterHM_ES_PMSw1_Pl($id) );
    }
/** AB HIER WIEDER �NDERN **/

/**Hier sind zu �berwachende Ger�te einzustellen.
D.h. es wird eine PUSH-Mitteilung auf dein Handy geschickt, sobald das Ger�t im Standby oder ausgeschaltetem Zustand ist.
Und es erfolgt eine PUSH-Mitteilung wenn das Ger�t "l�uft". In diesem Beispiel ist eine Siemens Waschmaschine mit einer Standby-Schaltung von unter 4 Watt Verbrauch
und eine "l�uft"-Schaltung ab 7 Watt eingestellt. Das Skript pr�ft mehrmals auf Unter-/�berschreiten der Schwellen im Tug-Of-War-Verfahren um Fehlmeldungen bei kurzzeitigem
Unter-/�berschreiten zu verhindern. Dadurch erfolgt die PUSH-Mitteilung zeitversetzt. (ca. 2 Minuten)

SYNTAX: $energymanager-> registerDevice(GER�TENAME PUSH MITTEILUNG, INSTANZID DES POWERMETERS (WATTVERBRAUCH), STANDBYGRENZE IN WATT, EINGESCHALTETGRENZE IN WATT, GER�TENAME, GER�TETYP);
**/
$energymanager-> registerDevice("Waschmaschine", 22517 /*[Hardware\Keller\Waschraum\Waschmaschine\POWERMETER]*/, 4, 7, "Siemens", "IQ-800");
$energymanager-> registerDevice("Trockner", 14379 /*[Hardware\Keller\Waschraum\Trockner\POWERMETER]*/, 2, 10, "Bosch", "");
?>