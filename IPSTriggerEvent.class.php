<?
/**
* IPS Trigger Event class
*
* This configures ips trigger events and manages them
*
* @link https://github.com/florianprobst/ips-library project website
*
* @author Florian Probst <florian.probst@gmx.de>
*
* @license GNU
* GNU General Public License, version 3
*/

/**
* class IPSTriggerEvent
*/
class IPSTriggerEvent{
	
	/**
	* ips id of the event
	*
	* @var int
	* @access private
	*/
	protected $id;

	/**
	* name of the event
	*
	* @var string
	* @access private
	*/
	protected $name;

	/**
	* id of the events parent
	* this defines where the event will be created
	*
	* @var int
	* @access private
	*/
	protected $parentId;
	
	/**
	* id of the events target / trigger instance
	* this defines the source / trigger which is observed and starts this event
	*
	* @var int
	* @access private
	*/
	protected $trigger;
	
		/**
	* events type
	*
	* @var int
	* @access private
	*/
	protected $type;
	
	/**
	* debug information
	* enables debug information for this class
	*
	* @var boolean
	* @access private
	*/
	private $debug;
	
	/**
	* IPS - trigger event type update
	* @const tUPDATE
	* @access private
	*/
	const tUPDATE = 0;
	
	/**
	* IPS - trigger event type change
	* @const tCHANGE
	* @access private
	*/
	const tCHANGE = 1;
	
	/**
	* IPS - trigger event type upper limit
	* @const tCAP
	* @access private
	*/
	const tCAP = 2;
	
	/**
	* IPS - trigger event type lower limit
	* @const tFLOOR
	* @access private
	*/
	const tFLOOR = 3;
	
	/**
	* IPS - trigger event type specific value
	* @const tVALUE
	* @access private
	*/
	const tVALUE = 4;
	
	/**
	* constructor
	*
	* @throws Exception if $type is not valid
	* @access public
	*/
	public function __construct($parentId, $trigger, $type, $name, $debug = false){
		if(!($type == self::tUPDATE || $type == self::tCHANGE || $type == self::tCAP || $type == self::tFLOOR || $type == self::tVALUE)){
			throw new Exception("Parameter \$type must be a valid trigger event type, see ip symcon documentation");
		}
		
		if($type == self::tCAP || $type == self::tFLOOR || $type == self::tVALUE){
			throw new Exception("Only TriggerEvents of type UPDATE and CHANGE are implemented yet.");
		}
		
		$this->parentId = $parentId;
		$this->trigger = $trigger;
		$this->type = $type;
		$this->name = $name;
		$this->debug = $debug;
		$this->id = @IPS_GetEventIDByName($this->name, $this->parentId);
		
		//check if event does already exist
		if($this->id == false){
			if($this->debug) echo "INFO - create IPS event $name\n";
			$this->id = IPS_CreateEvent(0);										//create trigger event and store id
			IPS_SetName($this->id, $this->name);										//set event name
			IPS_SetEventTrigger($this->id, $this->type, $this->trigger);	//configure event trigger
			IPS_SetParent($this->id, $this->parentId);							//move event to parent (this will be called when trigger occurs)
			IPS_SetInfo($this->id, "this event was created by script " . $_IPS['SELF'] . " which is part of the ips-library (https://github.com/florianprobst/ips-library)");
			$this->activate();
		}
	}
	
	/**
	* getName
	*
	* @return $string event name
	* @access public
	*/
	public function getName(){
		return $this->name;
	}
	
	/**
	* activate
	* enables this event
	*
	* @access public
	*/
	public function activate(){
		return IPS_SetEventActive($this->id, true);
	}
	
	/**
	* disable
	* disables this event
	*
	* @access public
	*/
	public function disable(){
		return IPS_SetEventActive($this->id, false);
	}
	
	/**
	* delete
	* deletes this event
	*
	* @access public
	*/
	public function delete(){
		return IPS_DeleteEvent($this->id);
	}
}
?>