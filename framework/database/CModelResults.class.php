<?
namespace Framework\Database;

/* Base DB Classes */
abstract class CModelResults implements \ArrayAccess, \Iterator
{
	protected $_query;    //The query object the results will work off of
	protected $_results;  //The actual results from the query
	protected $_class;    //The model class to use for model wrapping

	//Query properties
	private $_sort;
	private $_limit;
	private $_skip;

	/**
	 * Constructor initializes the CModelResults object.
	 */
	public function __construct(\Framework\Database\CModelQuery $query)
	{
		$this->_query   = $query;
		$this->_class   = $query->getClass();
		$this->_results = NULL;

		//Query properties
		$this->_sort  = NULL;
		$this->_limit = NULL;
		$this->_skip  = NULL;
	}

	/** Query Property Modifiers **/

	/**
	 * Methos sets a sort on the query.
	 * @param $sort The sorting property to set when querying.
	 * @returns \Framework\Database\CModelResults Returns itself.
	 */
	public function sort($sort)
	{
		$this->_sort = $sort;
		return $this;
	}

	/**
	 * Methos sets a limit on the query.
	 * @param $limit The limit property to set when querying.
	 * @returns \Framework\Database\CModelResults Returns itself.
	 */
	public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}

	/**
	 * Methos sets a skip on the query.
	 * @param $skip The skip property to set when querying.
	 * @returns \Framework\Database\CModelResults Returns itself.
	 */
	public function skip($skip)
	{
		$this->_skip = $skip;
		return $this;
	}
	/** END Query Property Modifiers **/

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
	/** END Query Property Accessors Modifiers **/

	/**
	 * Method sets the results for this object.
	 * @param mixed $results The results to manage.
	 */
	public function setResults($results)
	{
		$this->_results = $results;
	}

	/**
	 * Method retuns the model with values set.
	 * @param array $values The values to set into the new Model.
	 * @retuns \Framework\Database\CModel The model with the results.
	 */
	protected function _getModel(array $values)
	{
		$class = $this->_class;
		$model = $class::model($values);

		return $model;
	}
}
?>
