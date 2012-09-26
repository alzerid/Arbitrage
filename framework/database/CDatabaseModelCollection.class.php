<?
namespace Framework\Database;

/* Base DB Classes */
abstract class CDatabaseModelCollection implements \ArrayAccess, \Iterator
{
	protected $_query;       //The query object the results will work off of
	protected $_collection;  //The actual results from the query

	/**
	 * Constructor initializes the CDatabaseModelCollection object.
	 */
	public function __construct(\Framework\Database\CDriverQuery $query)
	{
		$this->_query   = $query;
		$this->_collection = NULL;

		//Query properties
		$this->_sort  = NULL;
		$this->_limit = NULL;
		$this->_skip  = NULL;
	}


	/** Query Property Accessors Modifiers **/

	/**
	 * Method returns the sorting property.
	 * @returns mixed The sorting property.
	 */
	public function getSort()
	{
		return $this->_sort;
	}

	/**
	 * Method returns the limit property.
	 * @returns mixed The limit property.
	 */
	public function getLimit()
	{
		return $this->_limit;
	}

	/**
	 * Method returns the skip property.
	 * @returns mixed The skip property.
	 */
	public function getSkip()
	{
		return $this->_skip;
	}

	/**
	 * Method returns the join property.
	 * @returns mixed The join property.
	 */
	public function getJoin()
	{
		return $this->_join;
	}
	/** END Query Property Accessors Modifiers **/

	/**
	 * Method sets the results for this object.
	 * @param mixed $collection The results to manage.
	 */
	public function setCollection($collection)
	{
		$this->_collection = $collection;
	}

	/**
	 * Method retuns the model with values set.
	 * @param array $values The values to set into the new Model.
	 * @retuns \Framework\Database\CModel The model with the results.
	 */
	protected function _getModel(array $values)
	{
		$class = $this->_query->getClass();
		$model = new $class($values, $this->_query->getDriver());

		return $model;
	}
}
?>
