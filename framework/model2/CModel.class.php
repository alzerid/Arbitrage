<?
namespace Arbitrage2\Model2;

abstract class CModel extends CModelData
{
	private $_idKey=NULL;

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

	static public function model(array $data, $class=NULL)
	{
		if($class === NULL)
			$class = get_called_class();

		//Create new Model
		$model = new $class;

		//Set data and normalize
		$model->_setData($data);
		$model->_normalizeData();

		return $model;
	}

	static public function query()
	{
		//Setup model object
		$driver = static::properties();
		$driver = $driver['driver'];

		//Return Query Object
		$class = 'Arbitrage2\Model2\C' . $driver . "ModelQuery";
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
		if(!isset($this->_id))
			throw new EModelException("Cannot update without an ID");

		//Grab $variables not originals
		$vars = $this->getUpdatedData();
		self::query()->update(array('_id' => $this->_id), $vars)->execute();

		//Merge variables to originals
		$this->_merge();
	}

	public function save()
	{
		$this->_merge();
		$vars = $this->getOriginalData();

		//Check if id is set
		if($this->_idKey !== NULL)
			$vars['_id'] = $this->_idKey;

		//Call
		$id = self::query()->save($vars)->execute();

		if($this->_idKey === NULL)
			$this->_idKey = $id;
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
}
?>
