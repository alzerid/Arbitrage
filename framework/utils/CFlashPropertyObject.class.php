<?
namespace Framework\Utils;
use \Framework\Utils\CArrayObject;

class CFlashPropertyObject
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

		$this->_old = new CArrayObject($_SESSION['_flash']);
		$this->_new = new CArrayObject();
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

	public function update()
	{
		$_SESSION['_flash'] = $this->toArray();
	}

	public function toArray()
	{
		return $this->_new->toArray();
	}
}
?>
