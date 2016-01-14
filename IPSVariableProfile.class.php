<?
/**
*  Variable Profile class
*
* This configures ips variable profiles and manages them
*
* @link https://github.com/florianprobst/ips-library project website
*
* @author Florian Probst <florian.probst@gmx.de>
*
* @license GNU
* GNU General Public License, version 3
*/

/**
* class IPSVariableProfile
*/
class IPSVariableProfile{
	/**
	* name of the variable profile
	*
	* @var string
	* @access private
	*/
	private $name;

	/**
	* data type (only IPS-Datatypes)
	* bool, int, float, string
	*
	* @var int
	* @access private
	*/
	private $type;

	/**
	* prefix of the variable profile
	* this helps to seperate profiles created by this class
	* to profiles created by other scripts or manually
	*
	* @var string
	* @access private
	*/
	private $prefix;

	/**
	* suffix of the variable profile
	* usually the variables unit (e.g. Wh, %, etc)
	*
	* @var string
	* @access private
	*/
	private $suffix;

	/**
	* value associations
	* links values to specific formats, e.g. 0 Wh => red)
	*
	* @var array with the following structure: [["val"], ["name"], ["icon"], ["color"]]
	* @access private
	*/
	private $assoc;

	/**
	* debug information
	* enables debug information for this class
	*
	* @var boolean
	* @access private
	*/
	private $debug = false;

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
	* constructor
	*
	* create the variable profile in symcon if it does not exist
	*
	* @param string $name name of the variable profile
	* @param int $type IPS datatype
	* @param string $prefix name prefix
	* @param string $suffix variable suffix (usually the value unit)
	* @param array $assoc value to format associations
	* @param boolean $debug enables / disables debug information
	* @throws Exception if the parameter \$type is not an IPS datatype
	* @access public
	*/
	public function __construct($name, $type, $prefix = "", $suffix = "", $assoc = NULL, $debug = false){
		$this->name = $name;
		$this->type = $type;
		$this->prefix = $prefix;
		$this->suffix = $suffix;
		$this->assoc = $assoc;
		$this->debug = $debug;

		if($type != self::tBOOL && $type != self::tINT && $type != self::tFLOAT && $type != self::tSTRING)
		throw new Exception("method __construct does not support profiles of type $type!");
		$this->create($name, $type, $prefix, $suffix, $assoc);
	}

	/**
	* create the variable profile in symcon if it does not exist
	*
	* @param string $name name of the variable profile
	* @param int $type IPS datatype
	* @param string $prefix name prefix
	* @param string $suffix variable suffix (usually the value unit)
	* @param array $assoc value to format associations
	* @access private
	*/
	private function create($name, $type, $prefix = "", $suffix = "", $assoc = NULL){
		if(!IPS_VariableProfileExists($name)){
			if($this->debug) echo "INFO - variable profile $name does not exist. It will be created.\n";
			IPS_CreateVariableProfile($name, $type);
			IPS_SetVariableProfileText($name, $prefix, $suffix);
			if(isset($assoc)){
				foreach($assoc as $a){
					if($this->debug) echo "INFO - variable profile association for variable $name and value ". $a["name"]." does not exist and will be created\n";
					IPS_SetVariableProfileAssociation($name, $a["val"], $a["name"], $a["icon"], $a["color"]);
				}
			}
		}
	}

	/**
	* delete the variable profile in symcon
	*
	* @access public
	*/
	public function delete(){
		IPS_DeleteVariableProfile($this->name);
	}
	
	/**
	* returns the variable profile name
	*
	* @return string profile name
	* @access public
	*/
	public function getName(){
		return $this->name;
	}
}
?>