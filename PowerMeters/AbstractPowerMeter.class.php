<?
/**
 * Basic abstract class that implements the interface IPowermeter
 *
 * This is a base implementation of the interface IPowerMeter for power meter devices.
 * Each different power meter device (e.g. different manufacturer or model) has to implement the
 * interface IPowerMeter. This abstract class supports basic IPS methods that should fit for all
 * IP-Symcon devices. (e.g. handling instanceId, etc.)
 * 
 * @link https://github.com/florianprobst/ips-energymanager project website
 * 
 * @author Florian Probst <florian.probst@gmx.de>
 * 
 * @license GNU
 * GNU General Public License, version 3
 */

require_once 'IPowerMeter.interface.php';

/**
* abstract class AbstractPowerMeter
* 
* @uses IPowerMeter as interface
*/
abstract class AbstractPowerMeter implements IPowerMeter{
	
	/**
  * IP-Symcon instance id of the power meter device
  *
  * @var int
  * @access private
  */
	private $instanceId;
	
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
	public function __construct($instanceId, $manufacturer = self::UNKNOWN_MANUFACTURER, $model = self::UNKNOWN_MODEL){
		if(!is_int($instanceId))
			throw new Exception("Parameter \$instanceId is not of type 'integer'.");
		$this->instanceId = $instanceId;
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
}
?>