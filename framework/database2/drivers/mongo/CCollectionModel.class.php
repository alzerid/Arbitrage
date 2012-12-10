<?
namespace Framework\Database2\Drivers\Mongo;

class CCollectionModel extends \Framework\Database2\Model\CCollectionModel
{
	protected $_idx;
	private $_cnt;

	public function __construct(\Framework\Database2\Model\CQueryModel $query_model, $results)
	{
		//Parent company
		parent::__construct($query_model, $results);

		//Set iterator value
		$this->_idx = -1;
	}

	/**
	 * Method returns the count of the collection.
	 * @return Returns the count.
	 */
	public function count()
	{
		return $this->_results->count();
	}

	/**************************/
	/** Array Access Methods **/
	/**************************/
	public function offsetExists($offer)
	{
		die(__METHOD__);
	}

	public function offsetGet($offset)
	{
		die(__METHOD__);
	}

	public function offsetSet($offset, $val)
	{
		die(__METHOD__);
	}

	public function offsetUnset($offset)
	{
		die(__METHOD__);
	}
	/******************************/
	/** End Array Access Methods **/
	/******************************/

	/*****************************/
	/** Iterator Implementation **/
	/*****************************/
	public function rewind()
	{
		$this->_idx = 0;
		$this->_results->rewind();
	}

	public function current()
	{
		//Get current
		$current = $this->_results->current();

		//Convert to Model Structure and DataTypes
		$this->_query_model->convertNativeToModel($current);

		//Convert the data into a model
		$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($this->_model);
		$model = $class::create($current);

		return $model;
	}

	public function key()
	{
		return $this->_idx;
	}

	public function next()
	{
		$this->_idx++;;
		$this->_results->next();
	}

	public function valid()
	{
		return $this->_results->valid();
	}
	/*********************************/
	/** End Iterator Implementation **/
	/*********************************/
}
?>
