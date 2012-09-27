<?
namespace Framework\Database;

class CDatabaseModel extends \Framework\Database\CModel implements \Framework\Interfaces\IDatabaseModel
{
	static private $_ID_KEYS = array();
	private $_idVal          = NULL;
	private $_properties     = NULL;

	public function __construct($data=NULL, \Framework\Interfaces\IDatabaseDriver $driver=NULL)
	{
		$this->_properties = self::properties();
		$this->setDriver($driver);

		//Call parent constructor
		parent::__construct($data);
	}

	static public function idKey()
	{
		$properties = self::properties();
		$class      = get_called_class();
		if(isset($properties['idKey']))
			self::$_ID_KEYS[$class] = $properties['idKey'];

		return self::$_ID_KEYS[$class];
	}

	//Loads the model specified into memory
	static public function load($class=NULL)
	{
		if($class === NULL)
			$class = get_called_class();

		return new $class;
	}

	/**
	 * Static method that returns the Driver Query object.
	 * @return Retuns the driver query object.
	 */
	static public function query()
	{
		return \Framework\Base\CKernel::getInstance()->getApplication()->getService('database')->getDriver(static::properties())->getQueryDriver(get_called_class());
	}

	static public function batch()
	{
		die(__METHOD__);
		//Setup model object
		$driver = static::properties();
		$driver = $driver['driver'];

		//Return Query Object
		$class = 'Arbitrage2\Database\C' . $driver . "ModelBatch";
		$batch = new $class(get_called_class());

		return $batch;
	}

	//Static methods needed to be overridden
	static public function properties()
	{
		//TODO: Get driver defaults from config --EMJ
		return static::properties();
	}

	/**
	 * Method sets the database to use for this model.
	 * @param string The database to use.
	 */
	public function setDatabase($database)
	{
		$this->_properties['database'] = $database;
	}

	/**
	 * Method sets the table to use for this model.
	 * @param string The table to use.
	 */
	public function setTable($table)
	{
		$this->_properties['table'] = $table;
	}

	/**
	 * Method returns the properties for this Model.
	 * @return array The model properties to return.
	 */
	public function getProperties()
	{
		return $this->_properties;
	}

	/***********************************/
	/** IDatabaseModel Implementation **/
	/***********************************/

	/* Update Methods */
	public function update()
	{
		//Ensure _id is there
		if(!isset($this->_idVal))
			throw new EModelException("Cannot update without an ID.");

		//Grab $variables not originals
		$vars = $this->getUpdateQuery();

		$key  = self::$_ID_KEYS[get_called_class()];
		$id   = $this->_driver->convertModelIDToNativeID($this->_idVal);

		//Query
		if($vars !== NULL)
			self::query()->update(array($key => $id), $vars);

		//Merge variables to originals
		$this->merge();
	}

	public function upsert(array $keys)
	{
		die(__METHOD__);
		if(count($keys) <= 0)
			throw new \Framework\Exceptions\EModelDataException("Keys must be specified for an upsert.");

		//Merge the variables
		$this->_merge();

		//setup query
		$query = array();
		foreach($keys as $key)
			$query[$key] = $this->_originals[$key];
	
		//Upsert
		self::query()->upsert($query, $this->toArray())->execute();
	}

	public function insert()
	{
		die(__METHOD__);
	}

	public function save()
	{
		//TODO: Remove database and table parameters. User model properties for these actions --EMJ

		//Get query driver
		$query = self::query();
		$prop  = $this->getProperties();
		
		if(isset($prop['database']))
			$query->getDriver()->setDatabase($prop['database']);

		if(isset($prop['table']))
			$query->getDriver()->setTable($prop['table']);

		//Set driver for model
		$this->_driver = $query->getDriver();

		//Get variables
		$this->merge();
		$vars = $this->getQuery();

		//Check if id is set
		if($this->_idVal !== NULL)
			$vars[self::$_ID_KEYS[get_called_class()]] = $this->_driver->convertModelDataTypeToNativeDataType($this->_idVal);

		//Save using the Query Driver
		$id = $query->save($vars);

		//Get id an set it to model
		if($this->_idVal === NULL)
			$this->_idVal = $id;
	}
	/* End Update Methods */

	public function remove()
	{
		if(empty($this->_idVal))
			throw new EModelException("Cannot update without an ID");

		self::query()->remove(array('_id' => $this->_idVal))->execute();
	}

	/**
	 * Method returns the ID of the model.
	 * @return Returns the id of the model.
	 */
	public function getID()
	{
		return $this->_idVal;
	}

	/***************************************/
	/** END IDatabaseModel Implementation **/
	/***************************************/

	/**
	 * Method converts the database model to a simple \Framework\Model\CModel
	 * @return Returns the \Framework\Model\CModel representation of this DatabaseModel.
	 */
	public function convertToBaseModel()
	{
		//Get model
		$model = parent::convertToBaseModel();

		//Set ID
		$key = self::$_ID_KEYS[get_called_class()];
		$model[$key] = $this->getID();

		return $model;
	}

	/*public function equals(UseModel $model)
	{
		//TODO: _id is mongo baseed, should not be! --EMJ
		return (($this->_id !== NULL && $model->_id !== NULL) && ($this->_id == $model->_id));
	}*/


	//Ovverride __get, __isset to ensure _id
	protected function _getData($name)
	{
		if($name == "_id")
			return $this->_idVal;

		return parent::_getData($name);
	}

	protected function _issetData($name)
	{
		if($name == self::$_ID_KEYS[get_called_class()] && !empty($this->_idVal))
			return true;

		return parent::_issetData($name);
	}

	/**
	 * Method sets originals only if key exists in default.
	 * @param $data The data to set.
	 */
	protected function _setModelData($data)
	{
		//Create new Model
		$key = self::idKey();
		$id  = NULL;

		if($key && isset($data[$key]))
		{
			$id = (string) $data[$key];
			unset($data[$key]);
		}

		//Unset the _id and set locally if exists
		if($id !== NULL)
		{
			//Convert id if need be
			if(!($id instanceof \Framework\Database\DataTypes\CDatabaseIDDataType))
				$id = new \Framework\Database\DataTypes\CDatabaseIDDataType($id);
			elseif($this->_driver)
				$id = $this->_driver->convertNativeIDtoModelID($id);

			$this->_idVal = $id;
		}

		//Call parent
		parent::_setModelData($data);
	}
}
?>
