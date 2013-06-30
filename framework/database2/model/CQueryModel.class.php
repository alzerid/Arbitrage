<?php
namespace Framework\Database2\Model;

//TODO: Conver to use selector classes --EMJ

abstract class CQueryModel
{
	protected $_query_driver;   //Query Driver
	protected $_model;          //Model

	/**
	 * Method constructs a CQueryModel.
	 * @param $driver The driver to use.
	 * @param $model The model to instantiate and set.
	 */
	public function __construct(\Framework\Database2\Drivers\CQueryDriver $driver, $model)
	{
		$this->_query_driver = $driver;
		$this->_model        = $model;
	}

	/****************************/
	/** Start Selector Methods **/
	/****************************/

	/**
	 * Method finds on instance of an entry within the database.
	 * @param $query The query to use for finding the entry.
	 * @return Returns NULL or the Model entry.
	 */
	public function findOne($query=NULL)
	{
		//Convert query
		if($query)
			$this->convertModelQueryToNative($query);

		//Query
		$ret = $this->_query_driver->findOne($query)->execute();

		//Create model if we have an entry
		if($ret)
		{
			//Convert the returned results to model data types
			$this->convertNativeToModel($ret);

			//Create model
			$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($this->_model);
			$ret   = $class::create($ret);
		}

		return $ret;
	}

	/**
	 * Method finds all entries within the database.
	 * @param $query The query to use for finding the entries.
	 * @return Returns a CCollection class.
	 */
	public function findAll($query=NULL)
	{
		//Convert query
		if($query)
			$this->convertModelQueryToNative($query);

		//Send db call
		$ret        = $this->_query_driver->findAll($query)->execute();
		$collection = $this->_createCollection($ret);

		return $collection;
	}

	/**************************/
	/** End Selector Methods **/
	/**************************/

	/****************************/
	/** Start Modifier Methods **/
	/****************************/

	public function remove($condition)
	{
		//Convert the condition
		$this->convertModelQueryToNative($condition);

		//Remove
		$this->_query_driver->remove($condition)->execute();
	}

	public function save(&$data)
	{
		//copy data to ignore reference
		$d = $data;

		//unset
		//TODO: Use the $idKey!!
		if($d['_id'] === NULL || $d['_id']->getValue() === NULL)
			unset($d['_id']);

		//Convert the data to native type
		$this->convertModelQueryToNative($d);
		$new = !(array_key_exists('_id', $d));

		//Save
		$this->_query_driver->save($d)->execute();

		//Check if we need to set _id
		//TODO: Use the $idKey!!
		if($new)
			$data['_id'] = new \Framework\Database2\Model\DataTypes\CDatabaseID((string) $d['_id']);
	}

	public function update($cond, $data)
	{
		//Convert condition
		$this->convertModelQueryToNative($cond);
		
		//Convert the data
		$this->convertModelQueryToNative($data);

		//Update
		$this->_query_driver->update($cond, array('$set' => $data))->execute();
	}

	/**************************/
	/** End Modifier Methods **/
	/**************************/

	/**
	 * Method returns the query driver.
	 * @return \Framework\Database2\Drivers\CQueryDriver Returns the query driver.
	 */
	public function getQueryDriver()
	{
		return $this->_query_driver;
	}

	/** 
	 * Method returns the model associated with this query driver.
	 * @return Returns the model associated with this query driver.
	 */
	public function getModel()
	{
		return $this->_model;
	}

	/** 
	 * Method sets the database for this query driver.
	 * @param $database THe database to set to.
	 */
	public function setDatabase($database)
	{
		$this->_query_driver->setDatabase($database);
	}

	/**
	 * Method sets the table for this query.
	 * @param $table The table to set to.
	 */
	public function setTable($table)
	{
		$this->_query_driver->setTable($table);
	}

	/**
	 * Method converts a native DataTypes into model DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	abstract public function convertNativeToModel(array &$data);

	/**
	 * Method converts a model DataTypes into native DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	abstract public function convertModelToNative(array &$data);

	/**
	 * Method converts a model query DataTypes into native DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	abstract public function convertModelQueryToNative(array &$data);

	/**
	 * Method converts query array values to the correct types associated with the model.
	 * @param $query The query to modify.
	 */
	protected function _convertNormalToModel(array &$data)
	{
		$model    = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($this->_model);
		var_dump($model::defaults());
		die();
		foreach($data as $key => $val)
		{
			var_dump($key, $val, $this->_model);
			die("IN");
		}
		var_dump($data);
		die(__METHOD__);
	}

	/**
	 * Method creates a collection class with the results.
	 * @param $results The results to associate the collection class with.
	 * @return \Framework\Database2\Model\CCollectionModel Returns a new collection model.
	 */
	private function _createCollection($results)
	{
		//Get type
		$type = ucwords($this->_query_driver->getDriver()->getDriverType());
		return \Framework\Base\CKernel::getInstance()->instantiate("Framework.Database2.Drivers.$type.CCollectionModel", array($this, $results));
	}
}
?>
