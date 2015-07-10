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
	public function __construct($parentId, $archiveId, $prefix = "EM_", $debug = false){
		$this->parentId = $parentId;
		$this->archiveId = $archiveId;
		$this->debug = $debug;
		$this->prefix = $prefix;
		//create variable profiles
		array_push($this->variableProfiles, new EnergyVariableProfile($this->prefix . "Watthours", self::tFLOAT, "", " Wh", NULL, $this->debug));
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
			"current_consumption" => new EnergyVariable($this->prefix . "Current_Consumption_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentId, NULL, $this->variableProfiles[0], false, $this->archiveId, $this->debug),
			"energy_counter" =>new EnergyVariable($this->prefix . "Energy_Counter_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentId, NULL, $this->variableProfiles[0], false, $this->archiveId, $this->debug),
			"energy_counter_last_read" => new EnergyVariable($this->prefix . "Energy_Counter_last_read_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentId, NULL, $this->variableProfiles[0], false, NULL, $this->debug)
		);
		
		array_push($this->powermeters, $tmp);
		
		return true;
	}
	
	
	/**
	* average power consumption per month in watt hours
	*
	* @param integer $startTimestamp starting time / date as UNIX Timestamp
	* @param integer $endTimestamp ending time / date as UNIX Timestamp
	* @param integer $limit max count of data sets (0 = no limit, but there is a hard-coded 10000 records limit which cant be exceeded)
	* @throws Exception if logging is not enabled for this variable
	* @throws Exception if param $variable is not of type EnergyVariable
	* @return float average power consumption per month in watt hours
	* @access public
	*/
	public function getAverageWattsPerMonth($variable, $startTimestamp, $endTimestamp, $limit = 0){
		if($variable->isLoggingEnabled() == false)
		throw new Exception("Logging is not enabled for this variable '".$variable->getName()."'");
		if(!($variable instanceof EnergyVariable))
		throw new Exception("Parameter \$variable is not of type EnergyVariable");
		$values = AC_GetAggregatedValues($variable->getArchiveId(), $variable->getId(), 2, $startTimestamp, $endTimestamp, $limit);
		print_r($values);
		return $values;
	}

	public function test(){
		foreach($this->powermeters as &$p){
			echo "result:" . $p["device"]->getCurrentWatts() ."\n";
			$this->getAverageWattsPerMonth($p["current_consumption"], strtotime("1 January 2015"), strtotime("now"));
		}
	}
}
?>