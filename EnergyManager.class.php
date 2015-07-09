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
 
require_once 'PowerMeters/IPowerMeter.interface.php';
require_once 'PowerMeters/HomeMaticPowerMeterHM_ES_PMSw1_Pl.class.php';
require_once 'VariableManagement/EnergyVariable.class.php';
require_once 'VariableManagement/EnergyVariableProfile.class.php';
 
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
	
	/**
	* Constructor
	* 
	* @access public
	*/
	public function __construct(){
		
	}
	
	/**
	* registerPowerMeter
	* 
	* @return boolean true if register was successful
	* @access public
	*/
	public function registerPowerMeter($powermeter){
		if(!($powermeter instanceof IPowerMeter))
			throw new Exception("Parameter \$powermeter is not of type IPowerMeter");
		array_push($this->powermeters, $powermeter);
		return true;
	}
	
	public function createVariableProfiles(){
		
	}
	
	public function test(){
		foreach($this->powermeters as $p){
			echo "result:" . $p->getCurrentWatts() ."\n";
		}
	}
}
?>