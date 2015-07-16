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
 * The power meter name of this model will be read from the power plug since it's most likely
 * that the IPS user names the power plug according to the device he wants to switch on or of.
 * This class searches for the channel 1 device using the unique homematic power meter serial number.
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
  * HomeMatic unique serial number / id without channel
  *
  * @var string
  * @access private
  */
	private $address;
	
	
	/**
  * HomeMatic address channel
  *
  * @const integer
  * @access private
  */
	const CHANNEL = 2;
	
	/**
  * HomeMatic power plug device instance id of this power meter
  *
  * @var int
  * @access private
  */
	private $plugId;
	
	/**
  * HomeMatic address channel of the plug device
  *
  * @const integer
  * @access private
  */
	const PLUG_CHANNEL = 1;
	
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
	* @throws Exception if there was no power plug device for this power meter found
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
		
		//try to find channel 1 of this device using the unique homematic power meter serial number.
		//we need this to find the name of the power plug which will be the name/description of this power meter device
		$tmpAddress = $this->getAddress($this->instanceId);
		$this->address = substr($tmpAddress,0,strlen($tmpAddress)-2);
		$hm_instances = IPS_GetInstanceListByModuleID (self::MODULE_ID);
		$this->plugId = false;
		foreach($hm_instances as $hm_instance){
			if($this->address . ":" . self::PLUG_CHANNEL == $this->getAddress($hm_instance)){
				$this->plugId = $hm_instance;
				break;
			}
		}
		if($this->plugId == false)
			throw new Exception("There was no power plug device instance for this power meter device (instance: " . $this->instanceId .") found! Please check if the device does exist in IPS console");
		//set the power plug's name for this device
		$this->name = IPS_GetName($this->plugId);
		
		//if no exception was thrown everything should be fine.
	}
	
	/**
	* getAddress
	* 
	* @param int $instanceId the instance id of the homematic device
	* @return string unique homematic address / serial number / id + channel
	* @access private
	*/
	private function getAddress($instanceId){
		$conf = IPS_GetConfiguration($instanceId);
		$json = json_decode($conf, true);
		return $json["Address"];
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
	
		
	/**
	* getEnergyCounterInstanceId
	* 
	* @return integer instance id of the energy counter variable
	* @access public
	*/
	public function getEnergyCounterInstanceId(){
		return $this->counterId;
	}
	
	/**
	* getCurrentConsumptionInstanceId
	* 
	* @return integer instance id of the current consumption variable
	* @access public
	*/
	public function getCurrentConsumptionInstanceId(){
		return $this->powerId;
	}
}
?>