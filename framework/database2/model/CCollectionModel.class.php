<?php
namespace Framework\Database2\Model;

abstract class CCollectionModel implements \ArrayAccess, \Iterator
{
	protected $_query_driver;   //Query driver
	protected $_results;        //Results
	protected $_model;          //Model

	public function __construct(\Framework\Database2\Drivers\CQueryDriver $driver, $results, $model)
	{
		$this->_query_driver = $driver;
		$this->_results      = $results;
		$this->_model        = $model;
	}

	/**
	 * Method returns the count of the collection.
	 * @return Returns the count.
	 */
	abstract public function count();

	/****************************/
	/** Query Driver Modifiers **/
	/****************************/

	/**
	 * Method limit  modifier for the query.
	 * @param $limit The limit.
	 */
	public function limit($limit)
	{
		$this->_query_driver->limit($limit);
		return $this;
	}

	/**
	 * Method sort modifier for the query.
	 * @param $sort The sort.
	 */
	public function sort($sort)
	{
		$this->_query_driver->sort($sort);
		return $this;
	}
	/********************************/
	/** End Query Driver Modifiers **/
	/********************************/
}
?>
