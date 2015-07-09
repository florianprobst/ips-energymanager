<?
/**
 * EnergyManager class
 *
 * This class manages all power meters (their counters, current consumption, power costs, etc.).
 * 
 * TODO: power failure methods, keep switch on, reporting, etc.
 * 
 * @link https://github.com/florianprobst/ips-energymanager project website
 * 
 * @author Florian Probst <florian.probst@gmx.de>
 * 
 * @license GNU
 * GNU General Public License, version 3
 */
 
require_once './PowerMeters/IPowerMeter.interface.php';
require_once './PowerMeters/HomeMaticPowerMeterHM_ES_PMSw1_Pl.class.php';
 
/**
* class EnergyManager
* 
* @uses IPowerMeter as power meter interface
*/
class EnergyManager{
	/**
  * array of managed power meter devices
  *
  * @var IPowerMeter
  * @access private
  */
	private $powermeters = array();
	
	public function __construct(){
		
	}
	
	public function registerPowerMeter($powermeter){
		if(!($powermeter instanceof IPowerMeter))
			throw new Exception("Parameter \$powermeter is not of type IPowerMeter");
		array_push($this->powermeters, $powermeter);
	}
	
	public function test(){
		foreach($powermeters as $p){
			print_r($p->getCurrentWatts());
		}
	}
}
?>