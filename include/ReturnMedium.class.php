<?
/**
  * Status responses must be: Success, Failure, TryAgain.
	*/

class ReturnMedium
{
	static public $RM_XML  = 0;
	static public $RM_JSON = 1;
	static public $RM_USER = 2;

	private $_skipheader;
	private $_scope;
	private $_errorno;
	private $_message;
	private $_user;
  private $_type;

	public function __construct($type=1)
	{
		$this->_skipheader = false;
    $this->_errorno    = 0;
    $this->_message    = "Success.";
    $this->_user       = NULL;
		$this->_scope      = "unknown";

    $this->setType($type);
	}

	public function parse($parse)
	{
		$this->_errorno = $parse[0];
		$this->_message = $parse[1];
		$this->_user    = (isset($parse[2])? $parse[2] : NULL);
	}

  public function setType($type)
  {
    if(is_string($type))
    {
      $type = strtolower($type);
      switch($type)
      {
        case 'json':
          $type = self::$RM_JSON;
          break;

        case 'xml':
          $type = self::$RM_XML;
          break;

        case 'user':
          $type = self::$RM_USER;
          break;
      }
    }

    //Set type
    $this->_type = $type;
  }

  public function display()
  {
    echo $this->render();
  }

	public function render()
	{
		$result = array();

    $result['header'] = array('scope' => $this->_scope, 'errorno' => $this->_errorno, 'message' => $this->_message);

		if($this->_user)
			$result['user'] = $this->_user;
		
		switch($this->_type)
		{
			case self::$RM_USER:
				//Encapsulate in APP_XML_NAME
				$arr = array(APP_XML_NAME => $result);
				$xml = new XMLDomConstruct('1.0', 'utf-8');

				if(!$this->_skipheader)
					header('Content-Type: text/xml');

				$xml->fromMixed($arr);
				$result = $xml->saveXML();
				break;

			case self::$RM_JSON:
				if(!$this->_skipheader)
					header('Content-Type: application/json');

				$result = json_encode($result);
				break;
		}

		return $result;
	}

	/** MODIFIERS **/
	public function setErrorNo($err)
	{
		$this->_errorno = $err;
	}
	
	public function setMessage($message)
	{
		$this->_message = $message;
	}
	
	public function setUser($user)
	{
		$this->_user = $user;
	}

	public function setSkipHeader($bool)
	{
		$this->_skipheader = $bool;
	}

	public function setScope($scope)
	{
		$this->_scope = $scope;
	}
	/**************/

	/** ACCESSORS **/
	public function getErrorNo()
	{
		return $this->_errorno;
	}
	
	public function getMessage()
	{
		return $this->_message;
	}
	
	public function getUser()
	{
		return $this->_user;
	}

	public function getScope()
	{
		return $this->_scope;
	}

	public function getType()
	{
		return $this->_type;
	}
	/***************/
}
?>
