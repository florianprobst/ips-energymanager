<?
/**
 * Implementation of the HomeMatic PowerMeter model HM-ES-PMSw1-Pl
 * 
 * This class supports the HomeMatic PowerMeter model HM-ES-PMSw1-Pl device connected to IP-Symcon.
 * 
 * This model has 3 channels:
 * * Channel 0 - MAINTENANCE:	contains HomeMatic maintenance variables (we dont need them)
 * * Channel 1 - POWERPLUG:		contains status variables of the power plug (we dont need them)
 * * Channel 2 - POWERMETER:	contains power meter variables (that's what we want)
 *		-> variable 'CURRENT' = current consumed at the moment in milliAmps
 *		-> variable 'ENERGY_COUNTER' = consumed watt-hours in total
 *		-> variable 'FREQUENCY' = power frequency in Hz
 *		-> variable 'POWER' = current watt consumption
 * 
 * @link https://github.com/florianprobst/ips-energymanager project website
 * 
 * @author Florian Probst <florian.probst@gmx.de>
 * 
 * @license GNU
 * GNU General Public License, version 3
 */

require_once 'AbstractPowerMeter.class.php';

/**
* class HomeMaticPowerMeterHM_ES_PMSw1_Pl
* 
* @uses AbstractPowerMeter as parent class
*/
class HomeMaticPowerMeterHM_ES_PMSw1_Pl extends AbstractPowerMeter{
	/**
	* device manufacturer
	* @const MANUFACTURER
  * @access private
	*/
	const MANUFACTURER = "HomeMatic";
	
	/**
	* device model
	* @const MODEL
  * @access private
	*/
	const MODEL = "HM-ES-PMSw1-Pl";
	
	/**
	* IPS module Id
	* 
	* a unique ID that IP-Symcon serves for each module type / manufacturer combination
	* 
	* @const MODULE_ID
  * @access private
	*/
	const MODULE_ID = "{EE4A81C6-5C90-4DB7-AD2F-F6BBD521412E}";
	
	/**
  * IP-Symcon instance id of the power variable containing
  * the current watt consumption
  *
  * @var float
  * @access private
  */
	private $powerId;
	
	/**
  * IP-Symcon instance id of the energy counter variable containing
  * the total counted energy consumption in watt hours
  *
  * @var float
  * @access private
  */
	private $counterId;
	
	/**
	* Constructor
	* 
	* @param int $powermeterInstanceId IP-Symcon instance id of the power meter device (in this case channel 2 of the device)
	* @throws Exception if the parameter \$powermeterInstanceId is not of type 'integer'
	* @throws Exception if the devices ModuleID is not a HomeMatic Device ModuleID'
	* @return HomeMaticPowerMeterHM_ES_PMSw1_Pl|null the object or null if an error occured
	* @access public
	*/
	public function __construct($powermeterInstanceId){
		parent::__construct($powermeterInstanceId, self::MANUFACTURER, self::MODEL);
		
		//check if it's the correct powermeterInstanceId
		
		//first we check if it's an HomeMatic Device
		$instance = IPS_GetInstance($this->getInstanceId());
		if($instance["ModuleInfo"]["ModuleID"] != self::MODULE_ID)
			throw new Exception("The device ModuleID does not match a HomeMatic Device. Please check if the IPS device instanceId is a HM-ES-PMSw1-Pl Device Channel 2");
		
		//second we check if an object with the ObjectIdent "POWER" exists
		$powerId = IPS_GetObjectIDByIdent('POWER', $this->getInstanceId());
		if($powerId == false)
			throw new Exception("There is no 'POWER' variable attached to the device with instanceId '".$this->getInstanceId()."'");
		$this->powerId = $powerId;
		
		//second we check if an object with the ObjectIdent "ENERGY_COUNTER" exists
		$counterId = IPS_GetObjectIDByIdent('ENERGY_COUNTER', $this->getInstanceId());
		if($counterId == false)
			throw new Exception("There is no 'ENERGY_COUNTER' variable attached to the device with instanceId '". $this->getInstanceId() ."'");
		$this->counterId = $counterId;
		
		//if no exception was thrown everything should be fine.
	}
	
	/**
	* getCurrentWatts
	* 
	* @return integer current watts consumed at the power meter
	* @access public
	*/
	public function getCurrentWatts(){
		return GetValue($this->powerId);
	}
	
	/**
	* getEnergyCounterWattHours
	* 
	* @return float energy counter in watt-hours
	* @access public
	*/
	public function getEnergyCounterWattHours(){
		return GetValue($this->counterId);
	}
}
?>