<?php
namespace Framework\Database2\Drivers;

abstract class CQueryDriver
{
	protected $_driver;     //The handle to the driver
	protected $_database;   //The database to query on
	protected $_table;      //The table to query on

	protected $_condition;  //The condition to use for the query
	protected $_data;       //Data associated with an action
	protected $_action;     //The action to take
	protected $_limit;      //Indicates a limit
	protected $_sort;       //Sorting

	/**
	 * Constructs a query driver.
	 * @param $driver The driver to attach to.
	 * @param $database The database to query on.
	 * @param $table The table to query on.
	 */
	public function __construct(\Framework\Database2\Drivers\CDriver $driver, $database, $table)
	{
		$this->_driver    = $driver;
		$this->_database  = $database;
		$this->_table     = $table;
		$this->_condition = NULL;
		$this->_data      = NULL;
		$this->_action    = NULL;

		//Setup modifiers
		$this->_limit = NULL;
		$this->_sort  = NULL;
	}

	/*************************/
	/**** Query Modifiers ****/
	/*************************/

	/**
	 * Limits the query.
	 * @param $limit The limit number.
	 */
	public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}

	/**
	 * Sorts the query.
	 * @param $sort The sort.
	 */
	public function sort($sort)
	{
		$this->_sort = $sort;
		return $this;
	}

	/*****************************/
	/**** End Query Modifiers ****/
	/*****************************/

	/**
	 * Method called to find all entries based on a query.
	 */
	public function findAll($query=NULL)
	{
		$this->_action    = "findAll";
		$this->_condition = $query;
		return $this;
	}

	/** 
	 * Method called to find one entry.
	 */
	public function findOne($query=NULL)
	{
		$this->_action    = "findOne";
		$this->_condition = $query;
		return $this;
	}

	/**
	 * Method called to save an entry.
	 */
	public function save($data)
	{
		$this->_action = 'save';
		$this->_data   = $data;
		return $this;
	}

	/**
	 * Method inserts new data to the DB.
	 */
	public function insert($data)
	{
		die(__METHOD__);
	}

	/**
	 * Method removes an item.
	 * @param $condition The condition we base our remove on.
	 */
	public function remove($condition=NULL)
	{
		$this->_action    = 'remove';
		$this->_condition = $condition;
		return $this;
	}

	/**
	 * Method executes the actual query.
	 */
	abstract public function execute();

	/**
	 * Method returns the driver.
	 * @return \Framework\Database2\Drivers\CDriver Returns the driver.
	 */
	public function getDriver()
	{
		return $this->_driver;
	}
}
?>
