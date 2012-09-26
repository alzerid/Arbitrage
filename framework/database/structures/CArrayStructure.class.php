<?
namespace Framework\Database\Structures;

class CArrayStructure extends \Framework\Model\Structures\CArrayStructure implements \Framework\Interfaces\IDatabaseModelStructure
{
	protected $_originals;
	private $_driver;

	public function __construct()
	{
		parent::__construct();
		$this->_originals = array();
		$this->_driver    = NULL;
	}

	/**
	 * Method instantiates the data type.
	 * @param $data The variables to set as default data for this Model.
	 * @param $class The class associated with the values.
	 */
	static public function instantiate($data=array(), $class=NULL)
	{
		$obj = parent::instantiate($data, $class);

		//Set originals
		if($data instanceof \Framework\Database\Structures\CArrayStructure)
			$obj->_originals = $data->_originals;
		else
			$obj->_originals = $obj->_data;

		return $obj;
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
	public function getUpdateQuery()
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
