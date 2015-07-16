<?
/**
 * Interface for power meter devices connected to IP-Symcon
 *
 * This interface describes the methods all power meter device abstraction layers
 * must implement
 * 
 * @link https://github.com/florianprobst/ips-energymanager project website
 * 
 * @author Florian Probst <florian.probst@gmx.de>
 * 
 * @license GNU
 * GNU General Public License, version 3
 */

/**
* interface IPowerMeter
*/
interface IPowerMeter{
	/**
	* getInstanceId
	* 
	* @return int IP-Symcon instance id of the power meter device
	* @access public
	*/
	public function getInstanceId();
	
	/**
	* getName
	* 
	* @return string name / description of the power meter
	* @access public
	*/
	public function getName();
	
	/**
	* getCurrentWatts
	* 
	* @return integer current watt consumed at the power meter
	* @access public
	*/
	public function getCurrentWatts();
	
	/**
	* getEnergyCounterWattHours
	* 
	* @return float energy counter in watt-hours
	* @access public
	*/
	public function getEnergyCounterWattHours();
	
	/**
	* getDeviceManufacturer
	* 
	* @return string device manufacturer
	* @access public
	*/
	public function getDeviceManufacturer();
	
	/**
	* getDeviceModel
	* 
	* @return string device model
	* @access public
	*/
	public function getDeviceModel();
	
	/**
	* setDeviceManufacturer
	* 
	* @param string $manufacturer name of the device manufacturer
	* @access public
	*/
	public function setDeviceManufacturer($manufacturer);
	
	/**
	* setDeviceModel
	* 
	* @param string $model name of the device model
	* @access public
	*/
	public function setDeviceModel($model);
	
	/**
	* getEnergyCounterInstanceId
	* 
	* @return integer instance id of the energy counter variable
	* @access public
	*/
	public function getEnergyCounterInstanceId();
	
	/**
	* getCurrentConsumptionInstanceId
	* 
	* @return integer instance id of the current consumption variable
	* @access public
	*/
	public function getCurrentConsumptionInstanceId();
}
?>