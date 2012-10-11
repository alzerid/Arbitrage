<?
namespace Framework\Database\Structures;

class CArrayStructure extends \Framework\Model\Structures\CArrayStructure implements \Framework\Interfaces\IDatabaseModelStructure
{
	protected $_originals;
	private $_driver;

	public function __construct($data=array(), $class=NULL)
	{
		parent::__construct($data, $class);
		$this->_originals = array();
		$this->_driver    = NULL;
	}

	/**
	 * Method returns if the needle is in the array.
	 * @param $needle The needle to search for.
	 * @return Returns true or false.
	 */
	public function contains($needle)
	{
		return in_array($needle, $this->_originals) || parent::contains($needle);
	}

	/**
	 * Method sets the driver being used.
	 * @param \Framework\Interfaces\IDatabaseDriver $driver The driver to set to.
	 */
	public function setDriver(\Framework\Interfaces\IDatabaseDriver $driver)
	{
		$this->_driver = $driver;
	}

	/**
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery($pkey=NULL)
	{
		throw new \Framework\Exceptions\EModelStructureException("Unable to get query without specific driver structure.");
	}

	/**
	 * Method returns the query expression.
	 * @return array Returns an array of the items.
	 */
	public function getQuery()
	{
		throw new \Framework\Exceptions\EModelStructureException("Unable to get query without specific driver structure.");
	}

	/**
	 * Method sets _originals to _data.
	 */
	public function merge()
	{
		$this->_originals = $this->_data;
	}

	/**
	 * Method clears the array back to it's original contents.
	 */
	public function clear()
	{
		die(__METHOD__);
		$this->_data = $this->_originals;
	}

	protected function _setModelData($vars)
	{
		parent::_setModelData($vars);
		$this->_originals = $vars;
	}
}
?>
