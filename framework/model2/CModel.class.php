<?
namespace Arbitrage2\Model2;

abstract class CModel extends CModelData
{
	static private $_ID_KEYS = array();
	private $_idVal=NULL;

	public function __construct(array &$originals=array(), array &$variables=array())
	{
		$class      = get_called_class();
		$properties = self::properties();
		if(isset($properties['idKey']))
			self::$_ID_KEYS[$class] = $properties['idKey'];

		parent::__construct($originals, $variables);
	}

	static public function loadDriver($driver)
	{
		$ucase = ucwords($driver);
		\CApplication::getInstance()->requireFrameworkFile("model2/$driver/C{$ucase}ModelQuery.class.php");
		\CApplication::getInstance()->requireFrameworkFile("model2/$driver/C{$ucase}ModelBatch.class.php");
		\CApplication::getInstance()->requireFrameworkFile("model2/$driver/C{$ucase}ModelResults.class.php");
	}

	//Loads the model specified into memory
	static public function load($class=NULL)
	{
		if($class === NULL)
			$class = get_called_class();

		new $class;
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

	/* Called when a form wants to set the _varables to the form
	   content. */
	static public function form(array $form)
	{
		die("FORM");
	}

	static public function query()
	{
		//Setup model object
		$driver = static::properties();
		$driver = $driver['driver'];

		//Return Query Object
		$class = 'Arbitrage2\Model2\C' . $driver . "ModelQuery";
		if(!class_exists($class))
			throw new EModelException("Unable to load query for '$driver'. Driver not loaded?");


		$query = new $class(get_called_class());

		return $query;
	}

	static public function batch()
	{
		//Setup model object
		$driver = static::properties();
		$driver = $driver['driver'];

		//Return Query Object
		$class = 'Arbitrage2\Model2\C' . $driver . "ModelBatch";
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
			throw new EModelException("Cannot update without an ID");

		//Grab $variables not originals
		$vars = $this->toArrayUpdated();
		$key  = self::$_ID_KEYS[get_called_class()];
		self::query()->update(array(self::$_ID_KEYS[get_called_class()] => $this->_idVal), $vars)->execute();

		//Merge variables to originals
		$this->_merge();
	}

	public function upsert(array $keys)
	{
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

	public function save()
	{
		$this->_merge();
		$vars = $this->toArray();

		//Check if id is set
		if($this->_idVal !== NULL)
			$vars[self::$_ID_KEYS[get_called_class()]] = $this->_idVal;

		//Call
		$id = self::query()->save($vars)->execute();

		if($this->_idVal === NULL)
			$this->_idVal = $id;
	}
	/* End Update Methods */

	public function remove()
	{
		self::query()->remove(array('_id' => $this->_idKey))->execute();
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
		if(!empty($this->_idVal))
			return $this->_idval;

		return parent::_issetData($name);
	}
}
?>
