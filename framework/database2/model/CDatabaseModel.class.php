<?php
namespace Framework\Database2\Model;

//TODO: Save typecasts

class CDatabaseModel extends \Framework\Database2\Model\CModel
{
	private $_database;   //Variable that override properties
	private $_table;      //Variable that ovveride properties

	/**
	 * Method constructs the model.
	 */
	public function __construct($data=array())
	{
		//Set variables
		$this->_database = NULL;
		$this->_table    = NULL;

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
	 * @param $database Database to use instead of what's in properties.
	 * @param $table Table to use instead of what's in properties.
	 * @return \Framework\Database2\CDatabaseQuery Returns a database query.
	 */
	static public function query($database=NULL, $table=NULL)
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

		//Setup database and table
		$database = (($database===NULL)? $properties['database'] : $database);
		$table    = (($table===NULL)? $properties['table'] : $table);

		//Create query object and CQueryModel
		$query = \Framework\Base\CKernel::getInstance()->instantiate("Framework.Database2.Drivers.$type.CQueryDriver", array($driver, $database, $table));
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
		$this->getQuery()->save($data);
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

	/**
	 * Method returns a query driver.
	 */
	public function getQuery()
	{
		return self::query($this->_database, $this->_table);
	}

	/**
	 * Method overrides the default database to use.
	 * @param $database The database to set to.
	 */
	public function setDatabase($database)
	{
		$this->_database = $database;
	}

	/**
	 * Method overrides the default table to use.
	 * @param $table The table to use.
	 */
	public function setTable($table)
	{
		$this->_table = $table;
	}
}
?>
