<?php
namespace Framework\Database2\Model;

class CQueryModel
{
	private $_query_driver;   //Query Driver
	private $_model;          //Model

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
			throw new \Framework\Exceptions\EDatabaseException("Unknown method call '$method'.");

		switch($method)
		{
			case 'findOne':
				$this->_query_driver->$method($args[0]);
				die(__METHOD__ . " FIND ONE");

				break;

			case 'findAll':
				$condition  = ((isset($args[0]))? $args[0] : NULL);
				$ret        = $this->_query_driver->$method($condition)->execute();
				$collection = $this->_createCollection($ret);

				return $collection;
		}
	}

	//TODO: Execute findOne, findAll, etc....

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
		$obj   = new $class($this->_query_driver, $results, $this->_model);

		//$args = array($this->_query_driver, $results, $this->_model);
		//$obj   = \Framework\Base\CKernel::getInstance()->instantiate("Framework.Database2.Drivers.$type.CCollectionModel");

		return $obj;
	}
}
?>
