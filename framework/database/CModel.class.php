<?
namespace Framework\Database;

abstract class CModel extends \Framework\Database\Types\CModelData implements \Framework\Interfaces\IDatabaseModel
{
	static private $_ID_KEYS = array();
	private $_idVal          = NULL;

	public function __construct(array &$originals=array(), array &$variables=array())
	{
		parent::__construct();

		$class      = get_called_class();
		$properties = self::properties();
		if(isset($properties['idKey']))
			self::$_ID_KEYS[$class] = $properties['idKey'];

		//Set model data
		$this->_setModelData($originals);
	}

	//Loads the model specified into memory
	static public function load($class=NULL)
	{
		if($class === NULL)
			$class = get_called_class();

		return new $class;
	}

	/* Called when one wants to create a model with the data
	   from the DB. */
	static public function model(array $data, $class=NULL)
	{
		if($class === NULL)
			$class = get_called_class();

		//Create new Model
		$object = new $class();

		//Unset the _id and set locally if exists
		if(isset($data['_id']))
		{
			$object->_idVal = $data['_id'];
			unset($data['_id']);
		}

		//Set database data into model
		$object->_setModelData($data);

		return $object;
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
		die("BATCH");
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

	static public function upgrade(CModel $model)
	{
		$class = get_called_class();
		var_dump($class);
		die("UPGRADE");
	}

	/* Update Methods */
	public function update()
	{
		//Ensure _id is there
		if(!isset($this->_idVal))
			throw new EModelException("Cannot update without an ID.");

		//Grab $variables not originals
		$vars = $this->toArrayUpdated();
		$key  = self::$_ID_KEYS[get_called_class()];
		self::query()->update(array(self::$_ID_KEYS[get_called_class()] => $this->_idVal), $vars);

		//Merge variables to originals
		$this->_merge();
	}

	public function upsert(array $keys)
	{
		die("CModel::upsert");
		if(count($keys) <= 0)
			throw new EModelDataException("Keys must be specified for an upsert.");

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
		die("CModel::insert");
	}

	public function save($database=NULL, $table=NULL)
	{
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
		die("CModel::remove");
		if(empty($this->_idVal))
			throw new EModelException("Cannot update without an ID");

		self::query()->remove(array('_id' => $this->_idVal))->execute();
	}

	public function getID()
	{
		return $this->_idVal;
	}

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
}
?>
