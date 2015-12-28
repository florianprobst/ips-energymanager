<?
/**
 * Implementation of the HomeMatic PowerMeter model HM-ES-PMSw1-Pl
 * 
 * This class supports the HomeMatic PowerMeter model HM-ES-PMSw1-Pl device connected to IP-Symcon.
 * 
 * This model has 3 channels:
 * * Channel 0 - MAINTENANCE:	contains HomeMatic maintenance variables (we dont need them)
 * * Channel 1 - POWERPLUG:		contains status variables of the power plug (we dont need them)
 * * Channel 2 - POWERMETER:	contains power meter variables (that's what we want)
 *		-> variable 'CURRENT' = current consumed at the moment in milliAmps
 *		-> variable 'ENERGY_COUNTER' = consumed watt-hours in total
 *		-> variable 'FREQUENCY' = power frequency in Hz
 *		-> variable 'POWER' = current watt consumption
 * 
 * The power meter name of this model will be read from the power plug since it's most likely
 * that the IPS user names the power plug according to the device he wants to switch on or of.
 * This class searches for the channel 1 device using the unique homematic power meter serial number.
 *
 * @link https://github.com/florianprobst/ips-energymanager project website
 * 
 * @author Florian Probst <florian.probst@gmx.de>
 * 
 * @license GNU
 * GNU General Public License, version 3
 */

require_once 'AbstractDevice.class.php';

/**
* class HomeMaticPowerMeterHM_ES_PMSw1_Pl
* 
* @uses AbstractPowerMeter as parent class
*/
class WashingMashine extends AbstractDevice{
}