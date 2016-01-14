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
require_once 'ips-library/IPSVariable.class.php';
require_once 'ips-library/IPSVariableProfile.class.php';
require_once 'Devices/IDevice.interface.php';

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
	* @var IPSVariableProfile
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
	* statistics variable: contains html to present the statistics and data from all power meters
	* handled by this class
	*
	* @var IPSVariable
	* @access private
	*/
	private $statistics;

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
		array_push($this->variableProfiles, new IPSVariableProfile($this->prefix . "Watthours", self::tFLOAT, "", " Wh", NULL, $this->debug));
		array_push($this->variableProfiles, new IPSVariableProfile("~HTMLBox", self::tFLOAT, "", "", NULL, $this->debug));
		$this->statistics = new IPSVariable($this->prefix . "Statistics", self::tSTRING, $this->parentId, $this->variableProfiles[1], false, NULL, 0, $this->debug);
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
			"current_consumption" => new IPSVariable($powermeter->getCurrentConsumptionInstanceId(), $this->variableProfiles[0], true, $this->archiveId, 0, $this->debug),
			"energy_counter" =>new IPSVariable($this->prefix . "Energy_Counter_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentId, $this->variableProfiles[0], false, $this->archiveId, 0, $this->debug),
			"energy_counter_last_read" => new IPSVariable($this->prefix . "Energy_Counter_last_read_" . $powermeter->getInstanceId(), self::tFLOAT, $this->parentId, $this->variableProfiles[0], false, NULL, 0, $this->debug)
		);
		
		array_push($this->powermeters, $tmp);
		
		return true;
	}
	
	/**
	* returns all power meters registered with this class
	*
	* @return array containing all power meters
	* @access public
	*/
	public function getPowerMeters(){
		return $this->powermeters;
	}
	
	/**
	* average power consumption per month in watt hours (only available datasets, if data covers only 6 days, only 6 days will be used)
	* on a 30 day base
	*
	* @param IPSVariable $variable logging enabled power consumption variable of the powermeter to check
	* @param integer $limit max count of data sets (0 = no limit, but there is a hard-coded 10000 records limit which cant be exceeded)
	* @throws Exception if logging is not enabled for this variable
	* @throws Exception if param $variable is not of type IPSVariable
	* @return float average power consumption per month in watt hours
	* @access public
	*/
	public function getAverageWattsByLastMonth($variable, $limit = 0){
		if(!($variable instanceof IPSVariable))
		throw new Exception("Parameter \$variable is not of type IPSVariable");
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
	* @param IPSVariable $variable logging enabled power consumption variable of the powermeter to check
	* @param integer $limit max count of data sets (0 = no limit, but there is a hard-coded 10000 records limit which cant be exceeded)
	* @throws Exception if logging is not enabled for this variable
	* @throws Exception if param $variable is not of type IPSVariable
	* @return float average power consumption per month in watt hours
	* @access public
	*/
	public function getAverageWattsByLastYear($variable, $limit = 0){
		if(!($variable instanceof IPSVariable))
		throw new Exception("Parameter \$variable is not of type IPSVariable");
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
	
	/**
	* collects all power meters counters and stores them in the energy manager variables
	* since some products do erase the counter value to 0 as soon as they lose power
	* we need to store the value in separate ips variables.
	*
	* @access public
	*/
	public function update(){
		foreach($this->powermeters as &$p){
			//current counter value from power meter (warning: depending on manufacturer / model this value
			//can be resetted to 0 when the device was disconnected.
			$current = $p["device"]->getEnergyCounterWattHours();
			
			//last read value stored to ips
			$last = $p["energy_counter_last_read"]->getValue();
			
			//the energy counter value we want to have
			$counter = $p["energy_counter"]->getValue();
			
			if($current < $last){
				//counter was reset (maybe power failure)
				$last = 0;
			}
			
			//calculate incremental value between last counter read and current counter read
			$increment = $current - $last;
			
			//add increment to the counter
			$counter += $increment;
			
			//save last read value to ips variable
			$p["energy_counter_last_read"]->setValue($current);
			
			//save counter value to ips variable
			$counter = $p["energy_counter"]->setValue($counter);
		}
		
		//now we have to create the statistics
		$this->statistics->setValue($this->createHTML());
	}
	
	/**
	* creates an html string containing the statistics table for all power meters
	*
	* @access private
	*/
	private function createHTML(){
		$doc = new DOMDocument();
		
		$html = "<html><head></head>";
			$html .= "<style>";
				$html .= "#em_table, #em_table tr, #em_table td { border: 1px solid black; border-collapse: collapse; }";
				$html .= "#em_thead { font-size: 14px; font-weight: normal }";
				$html .= "#em_tbody { font-size: 12px }"; 
				$html .= "#em_p { font-size: 10px }";
			$html .= "</style>";
			$html .= "<body>";
				$html .= "<table id='em_table' width='100%'>";
					$html .= "<thead id='em_thead'><tr>";
						$html .= "<td width='25%' rowspan='3'>";
							$html .= "Ger&auml;t";
						$html .= "</td>";
						$html .= "<td width='10%' rowspan='3'>";
							$html .= "Aktueller Verbrauch in Watt";
						$html .= "</td>";
						$html .= "<td width='10%' rowspan='3'>";
							$html .= "Z&auml;hlerstand in Kilowatt";
						$html .= "</td>";
							$html .= "<td width='55%' colspan='4'>";
								$html .= "Durchschnittsverbauch und Kosten je Monat";
							$html .= "</td>";
						$html .= "</tr>";
						$html .= "<tr>";
							$html .= "<td colspan='2'>";
								$html .= "der letzten 30 Tage";
							$html .= "</td>";
							$html .= "<td colspan='2'>";
								$html .= "der letzten 365 Tage";
							$html .= "</td>";
							$html .= "</tr>";
							$html .= "<tr>";
							$html .= "<td>";
								$html .= "Watt";
							$html .= "</td>";
							$html .= "<td>";
								$html .= "Kosten";
							$html .= "</td>";
							$html .= "<td>";
								$html .= "Watt";
							$html .= "</td>";
							$html .= "<td>";
								$html .= "Kosten";
							$html .= "</td>";
						$html .= "</tr></thead><tbody id='em_tbody'>";
						//start daten
						$total_costs_last_year = 0;
						foreach($this->powermeters as &$p){
							$name =  $p["device"]->getName();
							$current_watts = $p["device"]->getCurrentWatts();
							$counter = round($p["energy_counter"]->getValue() / 1000,2);
							$avgwatts_lastmonth = $this->getAverageWattsByLastMonth($p["current_consumption"]);
							$avgwatts_lastyear = $this->getAverageWattsByLastYear($p["current_consumption"]);
							$costs1 = $this->getCostsPerMonth($avgwatts_lastmonth);
							$costs2 = $this->getCostsPerMonth($avgwatts_lastyear);
							$total_costs_last_year += $costs2;
							
						$html .= "<tr>";
							$html .= "<td>";
								$html .= $name;
							$html .= "</td>";
							$html .= "<td>";
								$html .= $current_watts;
							$html .= "</td>";
							$html .= "<td>";
								$html .= $counter;
							$html .= "</td>";
							$html .= "<td>";
								$html .= $avgwatts_lastmonth;
							$html .= "</td>";
							$html .= "<td>";
								$html .= $costs1 . " &euro;";
							$html .= "</td>";
							$html .= "<td>";
								$html .= $avgwatts_lastyear;
							$html .= "</td>";
							$html .= "<td>";
								$html .= $costs2 . " &euro;";
							$html .= "</td>";
						$html .= "</tr>";
						}
						//end daten
				$html .= "</tbody></table>";
				$html .= "<p id='em_p'>Insgesamt belaufen sich die Kosten aller &uuml;berwachten Ger&auml;te auf: <b>" . $total_costs_last_year * 12 . " &euro; im Jahr</b> (Basis ist der Durschnittsverbrauch der letzten 365 Tage)</p>";
			$html .= "</body>";
		$html .= "</html>";
		
		$doc->loadHTML($html);
		$val = $doc->saveHTML();
		return $val;
	}
}
?>