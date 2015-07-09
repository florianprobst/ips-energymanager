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
	* array of all energymanager variables
	*
	* @var EnergyVariable
	* @access private
	*/
	private $variable = array();

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
	* @param string $prefix the variable name prefix to identify variables and variable profiles created by this script
	* @param boolean $debug enables / disables debug information
	* @access public
	*/
	public function __construct($parentId, $prefix = "EM_", $debug = false){
		$this->parentId = $parentId;
		$this->debug = $debug;
		$this->prefix = $prefix;
		$this->createVariableProfiles();
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
		//add new power meter to list
		array_push($this->powermeters, $powermeter);
		//create new variables for new power meter if they do not already exist
		//todo: enable logging for current watts
		//array_push($this->variables, new EnergyVariable($this.->prefix . "Current_Watts_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentid, NULL, $this->createVariableProfiles[0]));
		array_push($this->variables, new EnergyVariable($this.->prefix . "Energy_Counter_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentid, NULL, $this->createVariableProfiles[0]));
		array_push($this->variables, new EnergyVariable($this.->prefix . "Energy_Counter_last_read" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentid, NULL, $this->createVariableProfiles[0]));
		return true;
	}

	private function createVariableProfiles(){
		array_push($this->variableProfiles, new EnergyVariableProfile("Watthours", self::tFLOAT, $prefix = $this->prefix, $suffix = " Wh", $assoc = NULL, $debug = $this->debug);
	}

	private function createVariable($name, $type, $parent, $value = NULL, $profile = NULL){
		
	}

	public function test(){
		foreach($this->powermeters as $p){
			echo "result:" . $p->getCurrentWatts() ."\n";
		}
	}
}
?>