<?
/**
 * Interface for devices connected to the power meter (e.g. washing mashine, dryer, pumps, etc.)
 *
 * This interface describes the methods all device abstraction layers
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
interface IDevice{
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
	* @return string the consumer devices name (e.g. 'washing mashine')
	* @access public
	*/
	public function getName();
	
	/**
	* getState
	* 
	* @return boolean if the consumer device draws power
	* @access public
	*/
	public function getState();
	
	/**
	* getStandbyLevel
	* 
	* @return integer the consumer device consumption level while in standby
	* @access public
	*/
	public function getStandbyLevel();
	
	/**
	* getOnLevel
	* 
	* @return integer the minimum consumer device consumption level to state it as powered on
	* @access public
	*/
	public function getPowerOnLevel();
	
	/**
	* setStandbyLevel
	* 
	* @param integer the consumer device consumption level while in standby
	* @access public
	*/
	public function setStandbyLevel($watthours);
	
	/**
	* setOnLevel
	* 
	* @param integer the minimum consumer device consumption level to state it as powered on
	* @access public
	*/
	public function setPowerOnLevel($watthours);
	
	/**
	* getCurrentWatts
	* 
	* @return integer current watts consumed
	* @access public
	*/
	public function getCurrentWatts();
	
	/*
	public function getOnCyclesCurrentYear();
	
	public function getOnCyclesCurrentMonth();
	
	public function getOnCyclesCurrentDay();*/
}
?>