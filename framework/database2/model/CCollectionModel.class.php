<?php
namespace Framework\Database2\Model;

class CCollectionModel implements \ArrayAccess, \Iterator
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
	}

	public function current()
	{
	}

	public function key()
	{
	}

	public function next()
	{
	}

	public function valid()
	{
	}

	/*********************************/
	/** End Iterator Implementation **/
	/*********************************/





}
?>
