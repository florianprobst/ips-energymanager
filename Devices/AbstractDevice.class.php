<?
/**
 * Basic abstract class that implements the interface IDevice
 *
 * This is a base implementation of the interface IDevice for power consuming devices attached to a power meter.
 * This abstract class supports basic IPS methods that should fit for all
 * power consuming devices. (e.g. handling instanceId, names, etc.)
 * 
 * @link https://github.com/florianprobst/ips-energymanager project website
 * 
 * @author Florian Probst <florian.probst@gmx.de>
 * 
 * @license GNU
 * GNU General Public License, version 3
 */

require_once 'IDevice.interface.php';

/**
* abstract class AbstractPowerMeter
* 
* @uses IPowerMeter as interface
*/
abstract class AbstractDevice implements IDevice{
	
	/**
  * IP-Symcon instance id of the power meter device
  *
  * @var int
  * @access protected
  */
	protected $instanceId;
	
	/**
  * power meter name
  *
  * @var string
  * @access protected
  */
	protected $name;
	
	/**
  * power meter device manufacturer
  *
  * @var string
  * @access private
  */
	private $manufacturer;
	
	/**
  * power meter device model
  *
  * @var string
  * @access private
  */
	private $model;
	
	/**
	* unknown device manufacturer
	* @const UNKNOWN_MANUFACTURER
  * @access private
	*/
	const UNKNOWN_MANUFACTURER = "UNKNOWN";
	
	/**
	* unknown device manufacturer
	* @const UNKNOWN_MODEL
  * @access private
	*/
	const UNKNOWN_MODEL = "UNKNOWN";
	
	/**
	* Constructor
	* 
	* @param int $instanceId IP-Symcon instance id of the power meter device
	* @throws Exception if the parameter \$instanceId is not of type 'integer'
	* @return AbstractPowerMeter|null the object or null if an error occured
	* @access public
	*/
	public function __construct($instanceId, $name, $manufacturer = self::UNKNOWN_MANUFACTURER, $model = self::UNKNOWN_MODEL){
		if(!is_int($instanceId))
			throw new Exception("Parameter \$instanceId is not of type 'integer'.");
		$this->instanceId = $instanceId;
		$this->name = $name;
		$this->setDeviceManufacturer($manufacturer);
		$this->setDeviceModel($model);
	}
	
	/**
	* getInstanceId
	* 
	* @return int IP-Symcon instance id of the power meter device
	* @access public
	*/
	public function getInstanceId(){
		return $this->instanceId;
	}
	
	/**
	* getName
	* 
	* @return string name / description of the power meter
	* @access public
	*/
	public function getName(){
		return $this->name;
	}
	
	/**
	* getCurrentWatts
	* 
	* @return integer current watts consumed at the power meter
	* @access public
	*/
	abstract public function getCurrentWatts();
	
	/**
	* getEnergyCounterWattHours
	* 
	* @return float energy counter in watt-hours
	* @access public
	*/
	abstract public function getEnergyCounterWattHours();
	
	/**
	* getDeviceManufacturer
	* 
	* @return string device manufacturer
	* @access public
	*/
	public function getDeviceManufacturer(){
		return $this->manufacturer;
	}
	
	/**
	* getDeviceModel
	* 
	* @return string device model
	* @access public
	*/
	public function getDeviceModel(){
		return $this->model;
	}
	
		
	/**
	* setDeviceManufacturer
	* 
	* @param string $manufacturer name of the device manufacturer
	* @access public
	*/
	public function setDeviceManufacturer($manufacturer){
		$this->manufacturer = $manufacturer;
	}
	
	/**
	* setDeviceModel
	* 
	* @param string $model name of the device model
	* @access public
	*/
	public function setDeviceModel($model){
		$this->model = $model;
	}
	
	/**
	* getEnergyCounterInstanceId
	* 
	* @return integer instance id of the energy counter variable
	* @access public
	*/
	abstract function getEnergyCounterInstanceId();
	
	/**
	* getCurrentConsumptionInstanceId
	* 
	* @return integer instance id of the current consumption variable
	* @access public
	*/
	abstract function getCurrentConsumptionInstanceId();
}
?>