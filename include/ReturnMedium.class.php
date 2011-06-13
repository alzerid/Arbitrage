<?
define('RM_XML', 0);
define('RM_JSON', 1);
define('RM_USER', 2);

/**
  * Status responses must be: Success, Failure, TryAgain.
	*/

class ReturnMedium
{
	private $skipheader;
	private $errorno;
	private $message;
	private $user;
  private $type;

	public function __construct($type=RM_JSON)
	{
		$this->skipheader = false;
    $this->errorno    = 0;
    $this->message    = "Success.";
    $this->user       = NULL;

    $this->setType($type);
	}

	public function parse($parse)
	{
		$this->errorno = $parse[0];
		$this->message = $parse[1];
		$this->user    = (isset($parse[2])? $parse[2] : NULL);
	}

  public function setType($type)
  {
    if(is_string($type))
    {
      $type = strtolower($type);
      switch($type)
      {
        case 'json':
          $type = RM_JSON;
          break;

        case 'xml':
          $type = RM_XML;
          break;

        case 'user':
          $type = RM_USER;
          break;
      }
    }

    //Set type
    $this->type = $type;
  }

  public function display()
  {
    echo $this->render();
  }

	public function render()
	{
		$result = array();

    $result['header'] = array('errorno' => $this->errorno, 'message' => $this->message);

		if ($this->user) $result['user']  = $this->user;
		
		switch($this->type)
		{
			case RM_XML:
				//Encapsulate in APP_XML_NAME
				$arr = array(APP_XML_NAME => $result);
				$xml = new XMLDomConstruct('1.0', 'utf-8');

				if(!$this->skipheader)
					header('Content-Type: text/xml');

				$xml->fromMixed($arr);
				$result = $xml->saveXML();
				break;

			case RM_JSON:
				if(!$this->skipheader)
					header('Content-Type: application/json');

				$result = json_encode($result);
				break;
		}

		return $result;
	}

	/** MODIFIERS **/
	public function setErrorNo($err)
	{
		$this->errorno = $err;
	}
	
	public function setMessage($message)
	{
		$this->message = $message;
	}
	
	public function setUser($user)
	{
		$this->user = $user;
	}

	public function setSkipHeader($bool)
	{
		$this->skipheader = $bool;
	}

	/**************/

	/** ACCESSORS **/
	public function getErrorNo()
	{
		return $this->errorno;
	}
	
	public function getMessage($message)
	{
		return $this->message;
	}
	
	public function getUser($user)
	{
		return $this->user;
	}

	public function getType()
	{
		return $this->type;
	}
	/***************/
}
?>
