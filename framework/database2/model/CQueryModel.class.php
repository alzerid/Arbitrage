<?php
namespace Framework\Database2\Model;

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

	/**
	 * Method hijacked and properly calls query driver.
	 */
	public function __call($method, $args)
	{
		$valid = array('findOne', 'findAll');

		if(!in_array($method, $valid))
			throw new \Framework\Exceptions\EDatabaseDriverException("Unknown method call '$method'.");

		switch($method)
		{
			case 'findOne':
				//Get entry
				$ret = $this->_query_driver->$method($args[0])->execute();

				//set into model
				if($ret)
				{
					//Convert the returned results to model data types
					$this->_convertNativeToModel($ret);

					//Create model
					$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($this->_model);
					$ret   = $class::create($ret);
				}

				return $ret;

			case 'findAll':
				$condition  = ((isset($args[0]))? $args[0] : NULL);
				$ret        = $this->_query_driver->$method($condition)->execute();
				$collection = $this->_createCollection($ret);

				return $collection;
		}
	}

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
	 * Method creates a collection class with the results.
	 * @param $results The results to associate the collection class with.
	 * @return \Framework\Database2\Model\CCollectionModel Returns a new collection model.
	 */
	private function _createCollection($results)
	{
		//Get type
		$type  = ucwords($this->_query_driver->getDriver()->getDriverType());
		$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP("Framework.Database2.Drivers.$type.CCollectionModel");
		$obj   = new $class($this, $results);

		//$args = array($this->_query_driver, $results, $this->_model);
		//$obj   = \Framework\Base\CKernel::getInstance()->instantiate("Framework.Database2.Drivers.$type.CCollectionModel");

		return $obj;
	}
}
?>
