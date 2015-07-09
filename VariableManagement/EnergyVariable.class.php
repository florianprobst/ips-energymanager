<?
/**
* Energy Variable class
*
* This configures energy variables and manages them
*
* @link https://github.com/florianprobst/ips-energymanager project website
*
* @author Florian Probst <florian.probst@gmx.de>
*
* @license GNU
* GNU General Public License, version 3
*/

require_once('EnergyVariableProfile.php');

/**
* class EnergyVariable
* 
* @uses EnergyVariableProfile
*/
class EnergyVariable{
	/**
	* ips id of the variable
	*
	* @var int
	* @access private
	*/
	protected $id;

	/**
	* name of the variable
	*
	* @var string
	* @access private
	*/
	protected $name;

	/**
	* data type (only IPS-Datatypes)
	* bool, int, float, string
	*
	* @var int
	* @access private
	*/
	protected $type;

	/**
	* id of the variables parent
	* this defines where the variable will be created
	*
	* @var int
	* @access private
	*/
	protected $parent;

	/**
	* value of the variable
	*
	* @var mixed can be bool, int, float, string (check with \$type)
	* @access private
	*/
	protected $value;

	/**
	* value of the variable
	*
	* @var EnergyVariableProfile variable profile for this variable
	* @access private
	*/
	protected $profile;

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
	* create the variable in symcon if it does not exist
	*
	* @param string $name name of the variable
	* @param int $type IPS datatype
	* @param int $parent id of the variables parent, this defines where the variable will be created
	* @param mixed $value initially set a variable value
	* @param array $assoc value to format associations
	* @param boolean $debug enables / disables debug information
	* @throws Exception if the parameter \$profile is not an EnergyVariableProfile datatype
	* @access public
	*/
	public function __construct($name, $type, $parent, $value = NULL, $profile = NULL){
		if(isset($profile) && !($profile instanceof EnergyVariableProfile))
		throw new Exception("Parameter \$profile must be an instance of EnergyVariableProfile!");
		$this->name = $name;
		$this->type = $type;
		$this->parent = $parent;
		$this->profile = $profile;
		$this->value = $value;

		$this->id = @IPS_GetVariableIDByName($name, $parent);
		if($this->id == false){
			if($this->debug) echo "INFO - create IPS variable $name\n";
			$this->id = IPS_CreateVariable($this->type);
			IPS_SetName($this->id, $name);
			IPS_SetParent($this->id, $parent);
			IPS_SetInfo($this->id, "this variable was created by script " . $_IPS['SELF']);
			IPS_SetVariableCustomProfile($this->id, $profile->name);
		}
	}

	/**
	* sets the variable value
	*
	* @throws Exception if the value type does not match the variable type
	* @return true if value was set successful
	* @access public
	*/
	public function set($value){
		if($this->type == self::tBOOL && !is_bool($value))
		throw new Exception("(Variable ". $this->name .")Param 'value' is not a boolean.");
		if($this->type == self::tINT && !is_int($value))
		throw new Exception("(Variable ". $this->name .")Param 'value' is not an integer.");
		if($this->type == self::tFLOAT && !is_float($value))
		throw new Exception("(Variable ". $this->name .")Param 'value' is not a float.");
		if($this->type == self::tSTRING && !is_string($value))
		throw new Exception("(Variable ". $this->name .")Param 'value' is not a string.");
		$this->value = $value;
		SetValue($this->id, $value);
		return true;
	}

	/**
	* returns the variable id
	*
	* @return int variable id
	* @access public
	*/
	public function getId(){
		return $this->id;
	}

	/**
	* returns the variable value
	*
	* @return mixed variable value
	* @access public
	*/
	public function getValue(){
		return GetValue($this->id);
	}

	/**
	* returns the variable name
	*
	* @return string variable name
	* @access public
	*/
	public function getName(){
		return $this->name;
	}

	/**
	* returns the variable type
	*
	* @return int variable type
	* @access public
	*/
	public function getType(){
		return $this->type;
	}

	/**
	* returns the variable parent id
	*
	* @return int parent id
	* @access public
	*/
	public function getParent(){
		return $this->parent;
	}

	/**
	* returns the variable profile
	*
	* @return EnergyVariableProfile variable profile
	* @access public
	*/
	public function getProfile(){
		return $this->profiles;
	}

	/**
	* deletes the variable in ip-symcon
	*
	* @access public
	*/
	public function delete(){
		IPS_DeleteVariable($this->id);
	}
}
?>