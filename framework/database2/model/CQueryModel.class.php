<?php
namespace Framework\Database2\Model;

class CQueryModel
{
	private $_driver;   //Driver
	private $_model;    //Model

	/**
	 * Method constructs a CQueryModel.
	 * @param $driver The driver to use.
	 * @param $model The model to instantiate and set.
	 */
	public function __construct(\Framework\Database2\Drivers\CQueryDriver $driver, $model)
	{
		$this->_driver = $driver;
		$this->_model  = $model;
	}

	//TODO: Execute findOne, findAll, etc....
}
?>
