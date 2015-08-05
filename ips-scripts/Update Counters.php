<?
$config_script = 47194 /*[System\Skripte\EnergyManager\Config]*/; //instanz id des ip-symcon config skripts

require_once(IPS_GetScript($config_script)['ScriptFile']);

$energymanager->update();
?>
