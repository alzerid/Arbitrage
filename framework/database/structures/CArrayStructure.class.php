<?
namespace Framework\Database\Structures;

class CArrayStructure extends \Framework\Model\Structures\CArrayStructure implements \Framework\Interfaces\IDatabaseModelStructure
{
	protected $_originals;

	public function __construct()
	{
		parent::__construct();
		$this->_originals = array();
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
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery()
	{
		//TODO: Code smarter differences
		if(count($this->_data) == 0)
			return $this->_data;

		$ret = array_diff($this->_data, $this->_originals);
		if(count($ret) == 0)
			return NULL;

		return $this->_data;
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
