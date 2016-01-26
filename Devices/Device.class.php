<?
/**
 * Implementation
 * 
 *
 * @link https://github.com/florianprobst/ips-energymanager project website
 * 
 * @author Florian Probst <florian.probst@gmx.de>
 * 
 * @license GNU
 * GNU General Public License, version 3
 */

require_once 'AbstractDevice.class.php';
//require_once '/ips-library/IPSVariable.class.php';

/**
* class HomeMaticPowerMeterHM_ES_PMSw1_Pl
* 
* @uses AbstractPowerMeter as parent class
*/
class Device extends AbstractDevice{
	
	public function __construct($name, $powermeter, $standbylevel, $poweronlevel, $manufacturer = self::UNKNOWN_MANUFACTURER, $model = self::UNKNOWN_MODEL){
		parent::__construct($name, $powermeter, $standbylevel, $poweronlevel, $manufacturer, $model);
		
		//we need the following shit:
		//events checking the state of the washing machine.
		//a variable holding the running state of the washing machine
		//notification to cell phone apps
	}
}