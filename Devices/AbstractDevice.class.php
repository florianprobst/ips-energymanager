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
//require_once 'PowerMeters/IPowerMeter.interface.php';

/**
* abstract class AbstractPowerMeter
* 
* @uses IPowerMeter as interface
*/
abstract class AbstractDevice implements IDevice{
	/**
	* powermeter attached to this device
	*
	* @var IPowerMeter
	* @access private
	*/
	
	/**
  * power meter name
  *
  * @var string
  * @access protected
  */
	protected $name;
	
	/**
  * device manufacturer
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
  * stand by level
  *
  * level of watts which define the device being in stand by
  *
  * @var int
  * @access private
  */
	private $standbylevel;
	
	/**
  * power on level
  *
  * level of watts which define the device being in switched on
  *
  * @var int
  * @access private
  */
	private $poweronlevel;
	
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
	* device is 'on' constant
	* @const DEVICE_ON
	* @access public
	*/
	const DEVICE_ON = 1;
	
	/**
	* device is 'standby' constant
	*
	* @const DEVICE_STANDBY
	* @access public
	*/
	const DEVICE_STANDBY = 2;
	
	/**
	* device is 'off' constant
	* @const DEVICE_OFF
	* @access public
	*/
	const DEVICE_OFF = 3;
	
	/**
	* device 'on/off pending 1' constant
	*
	* we need to verify that the device is "on" over a certain time. there are fluctuations while running
	* which could trigger a short "0 watt consumption" which would throw a "device off" state we don't want.
	* To prevent this we got on and off pending constants numbered 1-5
	* @const DEVICE_PENDING_OFF/ON_1-5
	* @access public
	*/
	const DEVICE_PENDING_ON_1 = 11;
	const DEVICE_PENDING_ON_2 = 12;
	const DEVICE_PENDING_ON_3 = 13;
	const DEVICE_PENDING_ON_4 = 14;
	const DEVICE_PENDING_ON_5 = 15;
	const DEVICE_PENDING_OFF_1 = 21;
	const DEVICE_PENDING_OFF_2 = 22;
	const DEVICE_PENDING_OFF_3 = 23;
	const DEVICE_PENDING_OFF_4 = 24;
	const DEVICE_PENDING_OFF_5 = 25;
	
	/**
	* Constructor
	* 
	* @param int $instanceId IP-Symcon instance id of the power meter device
	* @throws Exception if the parameter \$instanceId is not of type 'integer'
	* @return AbstractPowerMeter|null the object or null if an error occured
	* @access public
	*/
	public function __construct($name, $powermeter, $standbylevel, $poweronlevel, $manufacturer = self::UNKNOWN_MANUFACTURER, $model = self::UNKNOWN_MODEL){
		if(!($powermeter instanceof IPowerMeter))
			throw new Exception("Parameter \$powermeter is not of type IPowerMeter");
		$this->powermeter = $powermeter;
		$this->standbylevel = $standbylevel;
		$this->poweronlevel = $poweronlevel;
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
	* @return integer current watts consumed at the devices power meter
	* @access public
	*/
	public function getCurrentWatts(){
		return $this->powermeter->getCurrentWatts();
	}

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
	* getState
	* 
	* @return int state of the device DEVICE_ON, DEVICE_STANDBY, DEVICE_OFF
	* @access public
	*/
	public function getState(){
		$consumption = $this->getCurrentWatts();
		if($consumption >= $this->getPowerOnLevel()){
			return self::DEVICE_ON;
		}elseif($consumption >= $this->getStandbyLevel()){
			return self::DEVICE_STANDBY;
		}elseif($consumption < $this->getStandbyLevel()){
			return self::DEVICE_OFF;
		}
	}
	
	/**
	* getStandbyLevel
	* 
	* @return integer the consumer device consumption level while in standby
	* @access public
	*/
	public function getStandbyLevel(){
		return $this->standbylevel;
	}
	
	/**
	* getPowerOnLevel
	* 
	* @return integer the minimum consumer device consumption level to state it as powered on
	* @access public
	*/
	public function getPowerOnLevel(){
		return $this->poweronlevel;
	}
	
	/**
	* setStandbyLevel
	* 
	* @param integer the consumer device consumption level while in standby
	* @access public
	*/
	public function setStandbyLevel($watthours){
		$this->standbylevel = $watthours;
	}
	
	/**
	* setPowerOnLevel
	* 
	* @param integer the minimum consumer device consumption level to state it as powered on
	* @access public
	*/
	public function setPowerOnLevel($watthours){
		$this->poweronlevel = $watthours;
	}

}
?>