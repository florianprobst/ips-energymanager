<?
/**
* IPS Variable class
*
* This configures ips variables and manages them
*
* @link https://github.com/florianprobst/ips-library project website
*
* @author Florian Probst <florian.probst@gmx.de>
*
* @license GNU
* GNU General Public License, version 3
*/

require_once('IPSVariableProfile.class.php');

/**
* class IPSVariable
*
* @uses IPSVariableProfile
*/
class IPSVariable{
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
	* @var LightSourceVariableProfile variable profile for this variable
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
	private $debug;

	/**
	* instance id of the archive control (usually located in IPS\core)
	*
	* @var integer
	* @access private
	*/
	private $archiveId;
	
	/**
	* enables / disables IPS logging features
	*
	* @var boolean
	* @access private
	*/
	private $enableLogging;
	
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

	public function __construct(){
		//try to evaluate which constructor fits to the arguments
		$argv = func_get_args(); //func_num_args()
		if (is_int($argv[0])){
			//most likely this is the instance id of an existing variable
			//match construct1
			
			$instanceId = $argv[0];
			if(isset($argv[1])){
				$profile = $argv[1];
			}else{
				$profile = NULL;
			}
			if(isset($argv[2])){
				$enableLogging = $argv[2];
			}else{
				$enableLogging = false;
			}
			if(isset($argv[3])){
				$archiveId = $argv[3];
			}else{
				$archiveId = NULL;
			}
			if(isset($argv[4])){
				$debug = $argv[4];
			}else{
				$debug = false;
			}
			self::__construct1($instanceId, $profile, $enableLogging, $archiveId, $debug);
		}
		
		if(is_string($argv[0])){
			//most likely this is the $name parameter for a new variable
			//but we doublecheck if the second parameter $type is given
			if($argv[1] == self::tBOOL || $argv[1] == self::tINT || $argv[1] == self::tFLOAT || $argv[1] == self::tSTRING){
				//datatype given
				//match construct 2	
				
				$name = $argv[0];
				$type = $argv[1];
				$parent = $argv[2];
				if(isset($argv[3])){
					$profiles = $argv[3];
				}else{
					$profiles = NULL;
				}
				if(isset($argv[4])){
					$enableLogging = $argv[4];
				}else{
					$enableLogging = false;
				}
				if(isset($argv[5])){
					$archiveId = $argv[5];
				}else{
					$archiveId = NULL;
				}
				if(isset($argv[6])){
					$debug = $argv[6];
				}else{
					$debug = false;
				}
				self::__construct2($name, $type, $parent, $profiles, $enableLogging, $archiveId, $debug);
			} 
		}
	}
	
	/**
	* constructor
	*
	* first constructor: used to bind an existing device variable to the power meter. 
	* e.g. the 'POWER' variable for HomeMatic HM-ES-PMSw1-Pl, there is no need to create a new variable, but
	* it's necessary to enable logging for it and provide a interface for that variable
	*
	* @param integer $instanceId instance id of the variable
	* @param IPSVariableProfile $profile variable profile for this variable
	* @param boolean $enableLogging enables or disables the ips functionality to log variable changes in a database
	* @param integer $archiveId instance id of the archive control (usually located in IPS\core)
	* @param boolean $debug enables / disables debug information
	*
	* @throws Exception if the parameter \$profile is not an IPSVariableProfile datatype
	* @access public
	*/	
	private function __construct1($instanceId, $profile = NULL, $enableLogging = false, $archiveId = NULL, $debug = false){
		if(isset($profile) && !($profile instanceof IPSVariableProfile))
		throw new Exception("Parameter \$profile must be an instance of IPSVariableProfile!");
		
		//if($debug) echo "Parameter \$enableLogging = $enableLogging\n";
		
		$obj = @IPS_GetObject($instanceId);
		if($obj == NULL)
		throw new Exception("Object with id '$instanceId' does not exist");
		$this->id = $instanceId;
		$this->name = $obj["ObjectName"];
		$this->parent = $obj["ParentID"];
		
		$var = IPS_GetVariable($this->id);
		if($profile->getName() != $var["VariableProfile"]){
			IPS_SetVariableCustomProfile($this->id, $profile->getName());
			IPS_SetInfo($this->id, "this variable was edited by script " . $_IPS['SELF'] . " - variable profile was set to '" . $profile->getName() ."'");
		}
		$this->profile = $profile;
		$this->enableLogging = $enableLogging;
		$this->archiveId = $archiveId;
		$this->debug = $debug;
		$this->verifyVariableLogging();
	}
	
	/**
	* constructor
	*
	* second constructor: create the variable in symcon if it does not exist
	*
	* @param string $name name of the variable
	* @param integer $type IPS datatype
	* @param integer $parent id of the variables parent, this defines where the variable will be created
	* @param mixed $value initially set a variable value
	* @param IPSVariableProfile $profile variable profile for this variable
	* @param boolean $enableLogging enables or disables the ips functionality to log variable changes in a database
	* @param integer $archiveId instance id of the archive control (usually located in IPS\core)
	* @param integer $aggregationType logging aggregation: 0 = gauge, 1 = counter
	* @param boolean $debug enables / disables debug information
	*
	* @throws Exception if the parameter \$profile is not an IPSVariableProfile datatype
	* @access public
	*/
	private function __construct2($name, $type, $parent, $profile = NULL, $enableLogging = false, $archiveId = NULL, $aggregationType = 0, $debug = false){
		if(isset($profile) && !($profile instanceof IPSVariableProfile))
		throw new Exception("Parameter \$profile must be an instance of IPSVariableProfile! \$name of the variable is '$name'");
		
		$this->name = $name;
		$this->type = $type;
		$this->parent = $parent;
		$this->profile = $profile;
		$this->enableLogging = $enableLogging;
		$this->archiveId = $archiveId;
		$this->aggregationType = $aggregationType;
		$this->debug = $debug;
		
		
		$this->id = @IPS_GetVariableIDByName($name, $parent);
		if($this->id == false){
			if($this->debug) echo "INFO - create IPS variable $name\n";
			$this->id = IPS_CreateVariable($this->type);
			IPS_SetName($this->id, $name);
			IPS_SetParent($this->id, $parent);
			IPS_SetInfo($this->id, "this variable was created by script " . $_IPS['SELF'] . " which is part of the ips-library (https://github.com/florianprobst/ips-library)");
			if(isset($profile)){
				IPS_SetVariableCustomProfile($this->id, $profile->getName());
			}
			$this->verifyVariableLogging();
		}
	}
	
	/**
	* checks if the variable logging state fits the settings
	* if not this method will update the variable logging
	*
	* @throws Exception if $archiveId is not set while logging is enabled
	* @access private
	*/
	private function verifyVariableLogging(){
		if ($this->enableLogging) {
				if($this->archiveId == NULL)
				throw new Exception("Parameter \$archiveId is not set but \$enableLogging is true");
				if($this->checkArchive($this->archiveId)){
					if(!AC_GetLoggingStatus($this->archiveId, $this->id)){
						AC_SetLoggingStatus($this->archiveId, $this->id, true);
						AC_SetAggregationType($this->archiveId, $this->id, $this->aggregationType);
						IPS_ApplyChanges($this->archiveId);
					}
				}
		}
		else{
			//todo: disable logging not implemented	
		}
	}

	/**
	* checks if the instance to $archiveId is a valid IPS archive object
	*
	* @param integer $archiveId instance id to be checked
	* @throws Exception if $archiveId does not match to IPS archive
	* @return true if id refers to an archive
	* @access private
	*/
	private function checkArchive($archiveId){
		$archive = @IPS_GetInstance($archiveId);
		if($archive == NULL)
		throw new Exception("Archive with instance id $archiveId does not exist");
		if($archive["ModuleInfo"]["ModuleID"] == "{43192F0B-135B-4CE7-A0A7-1475603F3060}")
		return true;
		return false;
	}

	/**
	* sets the variable value
	*
	* @throws Exception if the value type does not match the variable type
	* @return true if value was set successful
	* @access public
	*/
	public function setValue($value){
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
	* @return integer variable id
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
	* @return LightSourceVariableProfile variable profile
	* @access public
	*/
	public function getProfile(){
		return $this->profiles;
	}
	
	/**
	* returns the archive id
	*
	* @return integer archive id
	* @access public
	*/
	public function getArchiveId(){
		return $this->archiveId;
	}
	
	/**
	* returns if logging is enabled
	*
	* @return boolean enableLogging
	* @access public
	*/
	public function isLoggingEnabled(){
		return $this->enableLogging;
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