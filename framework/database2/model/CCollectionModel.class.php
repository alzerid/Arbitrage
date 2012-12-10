<?php
namespace Framework\Database2\Model;

abstract class CCollectionModel implements \ArrayAccess, \Iterator
{
	protected $_query_driver;   //Query driver
	protected $_query_model;    //Query model
	protected $_results;        //Results
	protected $_model;          //Model

	public function __construct(\Framework\Database2\Model\CQueryModel $query_model, $results)
	{
		$this->_query_model  = $query_model;
		$this->_query_driver = $this->_query_model->getQueryDriver();
		$this->_model        = $this->_query_model->getModel();
		$this->_results      = $results;
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
