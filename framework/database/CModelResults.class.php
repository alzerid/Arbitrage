<?
namespace Framework\Database;

/* Base DB Classes */
abstract class CModelResults implements \ArrayAccess, \Iterator
{
	protected $_results;
	protected $_class;

	public function __construct($results, $class)
	{
		$this->_results = $results;
		$this->_class   = $class;
	}

	public function toArrayObject()
	{
		$arr = new \ArrayObject();
		foreach($this as $result)
			$arr[] = $result;

		return $arr;
	}

	public function toArray()
	{
		$ret = array();
		foreach($this as $result)
			$ret[] = $result;

		return $ret;
	}

	protected function _getModel(array $arr)
	{
		$class = $this->_class;
		$model = $class::model($arr);

		return $model;
	}
}
?>
