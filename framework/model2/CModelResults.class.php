<?
namespace Arbitrage2\Model2;

/* Base DB Classes */
abstract class CModelResults implements \Iterator
{
	protected $_results;
	protected $_class;

	public function __construct($results, $class)
	{
		$this->_results = $results;
		$this->_class   = $class;
	}

	protected function _getModel(array $arr)
	{
		$class = $this->_class;
		$model = $class::model($arr);

		return $model;
	}
}
?>
