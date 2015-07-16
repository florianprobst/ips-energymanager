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
	* array of managed power meter devices and their variables
	*
	* @var IPowerMeter
	* @access private
	*/
	private $powermeters = array();

	/**
	* parent object id for all variables created by this script
	*
	* @var integer
	* @access private
	*/
	private $parentId;

	/**
	* variable name prefix to identify variables and variable profiles created by this script
	*
	* @var string
	* @access private
	*/
	private $prefix;

	/**
	* debug: enables / disables debug information
	*
	* @var boolean
	* @access private
	*/
	private $debug;

	/**
	* array of all energymanager variable profiles
	*
	* @var EnergyVariableProfile
	* @access private
	*/
	private $variableProfiles = array();

	/**
	* instance id of the archive control (usually located in IPS\core)
	*
	* @var integer
	* @access private
	*/
	private $archiveId;
	
	
	/**
	* pricing of 1 kWh
	*
	* @var float
	* @access private
	*/
	private $price_per_kwh;

	/**
	* IPS - datatype boolean
	* @const tBOOL
	* @access private
	*/
	const tBOOL = 0;

	/**
	* IPS - datatype integer
	* @const tINT
	* @access private
	*/
	const tINT = 1;

	/**
	* IPS - datatype float
	* @const tFLOAT
	* @access private
	*/
	const tFLOAT = 2;

	/**
	* IPS - datatype string
	* @const tSTRING
	* @access private
	*/
	const tSTRING = 3;

	/**
	* Constructor
	*
	* @param integer $parentId set the parent object for all items this script creates
	* @param integer $archiveId instance id of the archive control (usually located in IPS\core)
	* @param string $prefix the variable name prefix to identify variables and variable profiles created by this script
	* @param boolean $debug enables / disables debug information
	* @access public
	*/
	public function __construct($parentId, $archiveId, $price_per_kwh, $prefix = "EM_", $debug = false){
		$this->parentId = $parentId;
		$this->archiveId = $archiveId;
		$this->price_per_kwh = $price_per_kwh;
		$this->debug = $debug;
		$this->prefix = $prefix;
		//create variable profiles
		array_push($this->variableProfiles, new EnergyVariableProfile($this->prefix . "Watthours", self::tFLOAT, "", " Wh", NULL, $this->debug));
		array_push($this->variableProfiles, new EnergyVariableProfile("~HTMLBox", self::tFLOAT, "", "", NULL, $this->debug));
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
		
		//add new power meter to list, create variables and reference them to power meter		
		$tmp = array(
			"device" => $powermeter,
			"current_consumption" => new EnergyVariable($powermeter->getCurrentConsumptionInstanceId(), $this->variableProfiles[0], true, $this->archiveId, $this->debug),
			"energy_counter" =>new EnergyVariable($this->prefix . "Energy_Counter_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentId, $this->variableProfiles[0], false, $this->archiveId, $this->debug),
			"energy_counter_last_read" => new EnergyVariable($this->prefix . "Energy_Counter_last_read_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentId, $this->variableProfiles[0], false, NULL, $this->debug),
			"statistics" => new EnergyVariable($this->prefix . "Energy_statistics_" . $powermeter->getInstanceId(), self::tSTRING, $this->parentId, $this->variableProfiles[0], false, NULL, $this->debug)
		);
		
		array_push($this->powermeters, $tmp);
		
		return true;
	}
	
	/**
	* average power consumption per month in watt hours (only available datasets, if data covers only 6 days, only 6 days will be used)
	* on a 30 day base
	*
	* @param EnergyVariable $variable logging enabled power consumption variable of the powermeter to check
	* @param integer $limit max count of data sets (0 = no limit, but there is a hard-coded 10000 records limit which cant be exceeded)
	* @throws Exception if logging is not enabled for this variable
	* @throws Exception if param $variable is not of type EnergyVariable
	* @return float average power consumption per month in watt hours
	* @access public
	*/
	public function getAverageWattsByLastMonth($variable, $limit = 0){
		if(!($variable instanceof EnergyVariable))
		throw new Exception("Parameter \$variable is not of type EnergyVariable");
		if($variable->isLoggingEnabled() == false)
		throw new Exception("Logging is not enabled for this variable '".$variable->getName()."'");
		$startTimestamp = time()-24*60*60*30;
		$endTimestamp = time();
		$values = AC_GetAggregatedValues($variable->getArchiveId(), $variable->getId(), 3, $startTimestamp, $endTimestamp, $limit);
		return round($values[0]["Avg"],2);
	}
	
	/**
	* average power consumption per year in watt hours (only available datasets, if data covers only 6 month, only 6 month will be used)
	* on a 365 day base
	*
	* @param EnergyVariable $variable logging enabled power consumption variable of the powermeter to check
	* @param integer $limit max count of data sets (0 = no limit, but there is a hard-coded 10000 records limit which cant be exceeded)
	* @throws Exception if logging is not enabled for this variable
	* @throws Exception if param $variable is not of type EnergyVariable
	* @return float average power consumption per month in watt hours
	* @access public
	*/
	public function getAverageWattsByLastYear($variable, $limit = 0){
		if(!($variable instanceof EnergyVariable))
		throw new Exception("Parameter \$variable is not of type EnergyVariable");
		if($variable->isLoggingEnabled() == false)
		throw new Exception("Logging is not enabled for this variable '".$variable->getName()."'");
		$startTimestamp = time()-24*60*60*365;
		$endTimestamp = time();
		$values = AC_GetAggregatedValues($variable->getArchiveId(), $variable->getId(), 4, $startTimestamp, $endTimestamp, $limit);
		return round($values[0]["Avg"],2);
	}
	
	/**
	* calculates the power costs per day
	*
	* @param float $watts average power consumption
	* @return float power costs per day
	* @access public
	*/
	public function getCostsPerDay($watts){
		return round(($watts / 1000) * $this->price_per_kwh * 24, 2);
	}
	
	/**
	* calculates the power costs per month
	*
	* @param float $watts average power consumption
	* @return float power costs per month
	* @access public
	*/
	public function getCostsPerMonth($watts){
		return round(($watts / 1000) * $this->price_per_kwh * 24 * 30, 2);
	}
	
	/**
	* calculates the power costs per year
	*
	* @param float $watts average power consumption
	* @return float power costs per year
	* @access public
	*/
	public function getCostsPerYear($watts){
		return round(($watts / 1000) * $this->price_per_kwh * 24 * 365, 2);
	}

	public function test(){
		foreach($this->powermeters as &$p){
			$avgwatts_lastmonth = $this->getAverageWattsByLastMonth($p["current_consumption"]);
			$avgwatts_lastyear = $this->getAverageWattsByLastYear($p["current_consumption"]);
			$current_watts = $p["device"]->getCurrentWatts();
			echo "Name: " . $p["device"]->getName() . " \n";
			echo "current watts = " . $current_watts ." which means following costs: " . $this->getCostsPerMonth($current_watts) . " EUR per Month and " . $this->getCostsPerYear($current_watts) ." EUR per Year \n";
			echo "average watts based on last month = " . $avgwatts_lastmonth ." which means following costs: " . $this->getCostsPerMonth($avgwatts_lastmonth) . " EUR per Month and " . $this->getCostsPerYear($avgwatts_lastmonth) ." EUR per Year \n";
			echo "average watts based on last year = " . $avgwatts_lastyear ." which means following costs: " . $this->getCostsPerMonth($avgwatts_lastyear) . " EUR per Month and " . $this->getCostsPerYear($avgwatts_lastyear) ." EUR per Year \n";
			
		}
	}
}
?>