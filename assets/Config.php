<?
//Enthält die "globale" Konfiguration des EnergyManagers und wird von den anderen IPS-EnergyManager-Scripten aufgerufen.
//Hier werden auch die Instanz-IDs aller zu überwachenden Stromzähler angegeben.

require_once("../webfront/user/ips-energymanager/EnergyManager.class.php");

$configId = 45584  /*[System\IPS-EnergyManager\config]*/; //NICHT $_IPS['self'] benutzen, sondern ID dieses Scripts hier eintragen!
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
$energymanager = new EnergyManager($configId, $parentId, $archive_id, $price_per_kwh, $update_interval, $prefix, $debug);

foreach($id_array_homematic_powermeters_HM_ES_PMSw1_PL as &$id){
	$energymanager->registerPowerMeter( new HomeMaticPowerMeterHM_ES_PMSw1_Pl($id) );
}
?>