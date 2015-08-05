<?
//Enth�lt die "globale" Konfiguration des EnergyManagers und wird von den anderen IPS-EnergyManager-Scripten aufgerufen.
//Hier werden auch die Instanz-IDs aller zu �berwachenden Stromz�hler angegeben.

require_once("../webfront/user/ips-energymanager/EnergyManager.class.php");

$parentId = 54023 /*[System\Skripte\EnergyManager\Variables]*/; //Ablageort f�r erstellte Variablen
$price_per_kwh = 0.2378; // Preis pro Kilowattstunde deines Stromanbieters
$debug = true;
$prefix = "EM_";
$archive_id = 18531 /*[Archiv]*/; //Instanz ID des IPS-Archivs in welchem die Werte des Stromz�hlers geloggt werden sollen.

//Erg�nze alle IDs der zu �berwachenden Stromz�hler von Homematic (Typ HM_ES_PMSw1_PL) im nachfolgenden Array
$id_array_homematic_powermeters_HM_ES_PMSw1_PL = [
29221 /*[Hardware\Keller\Vorratskeller\QNAP\POWERMETER]*/
];


//ab hier nichts mehr �ndern
$energymanager = new EnergyManager($parentId, $archive_id, $price_per_kwh, $prefix, $debug);

foreach($id_array_homematic_powermeters_HM_ES_PMSw1_PL as &$id){
	$energymanager->registerPowerMeter( new HomeMaticPowerMeterHM_ES_PMSw1_Pl($id) );
}
?>
