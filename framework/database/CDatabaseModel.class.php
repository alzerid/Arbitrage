<?
namespace Framework\Database;

class CDatabaseModel extends \Framework\Database\CModel implements \Framework\Interfaces\IDatabaseModel
{
	static private $_ID_KEYS = array();
	private $_idVal          = NULL;

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
		return \Framework\Base\CKernel::getInstance()->getApplication()->getService('database')->getDriver(static::properties())->getQuery(get_called_class());
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

	public function save($database=NULL, $table=NULL)
	{
		die(__METHOD__);
		$this->_merge();
		$vars = $this->toArray();

		//Check if id is set
		if($this->_idVal !== NULL)
			$vars[self::$_ID_KEYS[get_called_class()]] = $this->_idVal;

		//Call
		$query= self::query();
		
		if($database)
			$query->getDriver()->setDatabase($database);

		if($table)
			$query->getDriver()->setTable($table);

		$id = $query->save($vars);

		if($this->_idVal === NULL)
			$this->_idVal = $id;
	}
	/* End Update Methods */

	public function remove()
	{
		die(__METHOD__);
		if(empty($this->_idVal))
			throw new EModelException("Cannot update without an ID");

		self::query()->remove(array('_id' => $this->_idVal))->execute();
	}

	public function getID()
	{
		return $this->_idVal;
	}

	/***************************************/
	/** END IDatabaseModel Implementation **/
	/***************************************/

	public function equals(UseModel $model)
	{
		//TODO: _id is mongo baseed, should not be! --EMJ

		return (($this->_id !== NULL && $model->_id !== NULL) && ($this->_id == $model->_id));
	}

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
			$this->_idVal = $this->_driver->convertNativeIDtoModelID($id);

		//Call parent
		parent::_setModelData($data);

		//Set data to defaults
		/*$this->_data = static::defaults();
		foreach($this->_data as $key=>$val)
		{
			//TODO: Handle CModel instances of $val
			if(!array_key_exists($key, $data))
				continue;

			if($this->_data[$key] instanceof \Framework\Model\CModel)
				$this->_data[$key]->_setModelData($data[$key]);
			else
				$this->_data[$key] = $val;
		}*/
	}
}
?>
