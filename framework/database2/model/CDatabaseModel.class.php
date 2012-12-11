<?php
namespace Framework\Database2\Model;

//TODO: Save typecasts

class CDatabaseModel extends \Framework\Database2\Model\CModel
{
	/**
	 * Method constructs the model.
	 */
	public function __construct($data=array())
	{
		//Get properties of model
		$properties = $this->_getProperties();
		$defaults   = static::defaults();

		//Set id from _idKey
		$properties = $this->_getProperties();
		$idKey      = $properties['idKey'];
		if(!isset($default[$idKey]))
			$defaults[$idKey] = new \Framework\Database2\Model\DataTypes\CDatabaseID;
		elseif(!($defaults[$idKey] instanceof \Framework\Database2\Model\DataTypes\CDatabaseID))
			throw new \Framework\Exceptions\EDatabaseDriverException("ID Key '{$defaults[$idKey]}' is not a CDatabaseID DataType.");

		//Set defaults
		\Framework\Model\CModel::__construct($defaults);

		//Ensure variables is in defaults
		$this->_setVariables($data);
	}

	/**
	 * Method returns a query object for querying the database.
	 * @return \Framework\Database2\CDatabaseQuery Returns a database query.
	 */
	static public function query()
	{
		//TODO: Cache query??

		//Grab the properties and determine what todo
		$properties = static::properties();
		$driver     = ((isset($properties['connection']))? $properties['connection'] : '_default');
		$driver     = self::$SERVICE->getDriver($driver);
		$properties = array_merge($driver->getProperties(), $properties);
		$type       = ucwords($driver->getDriverType());

		//Unset properties
		unset($properties['connection']);

		//Create query object and CQueryModel
		$query = \Framework\Base\CKernel::getInstance()->instantiate("Framework.Database2.Drivers.$type.CQueryDriver", array($driver, $properties['database'], $properties['table']));
		$model = \Framework\Base\CKernel::getInstance()->instantiate("Framework.Database2.Drivers.$type.CQueryModel", array($query, \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage(get_called_class())));

		return $model;
	}
	
	/**
	 * Method creates, converts raw database data into a database model.
	 */
	static public function create(array $data=array())
	{
		//Create model
		$class = get_called_class();
		$model = new $class($data);

		//Merge
		$model->merge();

		return $model;
	}

	/*************************************/
	/** Database Model Instance Methods **/
	/*************************************/

	/**
	 * Method saves the model into the database.
	 */
	public function save()
	{
		//Merge the data
		$this->merge();

		//If ID is 000 then remove
		$data = $this->_data;

		//unset
		if($data['_id']->getValue() === NULL)
			unset($data['_id']);

		//Save using the query model
		$this->query()->save($data);
	}

	/**
	 * Method inserts the model into the database.
	 */
	public function insert()
	{
		die(__METHOD__);
	}

	/**
	 * Method updates the data base entries from th emode.
	 */
	public function update()
	{
		die(__METHOD__);
	}
	/*****************************************/
	/** End Database Model Instance Methods **/
	/*****************************************/

}
?>
