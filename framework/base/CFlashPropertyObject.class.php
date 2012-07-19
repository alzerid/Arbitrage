<?
use Arbitrage2\Base;

class CFlashPropertyObject /*implements Iterator*/
{
	protected $_flash;
	protected $_new;
	protected $_old;

	public function __construct()
	{
		if(isset($_SESSION) && !isset($_SESSION['_flash']))
			$_SESSION['_flash'] = array();
		else if(!isset($_SESSION))
			$_SESSION = array('_flash' => array());

		$this->_old = new CPropertyObject($_SESSION['_flash']);
		$this->_new = new CPropertyObject();
	}

	public function __destruct()
	{
		$_SESSION['_flash'] = $this->_new->toArray();
	}

	public function __get($name)
	{
		$ret = $this->_new->$name;
		if($ret !== NULL)
			return $ret;

		$ret = $this->_old->$name;
		if($ret !== NULL)
			return $ret;
		
		return NULL;
	}

	public function __set($name, $val)
	{
		$this->_new->$name = $val;
	}

	public function toArray()
	{
		return $this->_new->toArray();
	}
}
?>
