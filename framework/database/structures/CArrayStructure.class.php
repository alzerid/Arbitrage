<?
namespace Framework\Database\Structures;

class CArrayStructure extends \Framework\Model\Structures\CArrayStructure implements \Framework\Interfaces\IDatabaseModelStructure
{
	protected $_originals;

	/**
	 * Method instantiates the data type.
	 * @param $data The variables to set as default data for this Model.
	 * @param $class The class associated with the values.
	 */
	static public function instantiate($data=array(), $class=NULL)
	{
		$obj = parent::instantiate($data, $class);
		$obj->_originals = $obj->_data;

		return $obj;
	}

	public function getUpdateQuery()
	{
		if(count($this->_data) == 0)
			return $this->_data;

		$ret = array_diff($this->_data, $this->_originals);
		if(count($ret) == 0)
			return NULL;

		return $ret;
	}

	public function clear()
	{
		$this->_data = $this->_originals;
	}
}
?>
