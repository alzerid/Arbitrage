<?
namespace Arbitrage2\DB2;

abstract class CModel extends CModelData
{
	private $_idKey;

	static public function loadDriver($driver)
	{
		$ucase = ucwords($driver);
		\CApplication::getInstance()->requireFrameworkFile("db2/$driver/C{$ucase}ModelQuery.class.php");
		\CApplication::getInstance()->requireFrameworkFile("db2/$driver/C{$ucase}ModelResults.class.php");
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
		$class = "Arbitrage2\DB2\\C" . $driver . "ModelQuery";
		$query = new $class(get_called_class());

		return $query;
	}

	//Static methods needed to be overridden
	static public function properties()
	{
		//TODO: Get driver defaults from config --EMJ
		return static::properties();
	}

	/* Update Methods */
	public function update()
	{
		//Ensure _id is there
		if(!isset($this->_id))
		{
			var_dump("IN UPDATE WITHOUT ID");
			die();
		}

		//Grab $variables not originals
		$vars = $this->getUpdatedData();
		self::query()->update(array('_id' => $this->_id), $vars)->execute();

		//Merge variables to originals
		$this->_merge();
	}

	public function save()
	{
		die("SAVE");
	}
	/* End Update Methods */

	public function equals(UseModel $model)
	{
		//TODO: _id is mongo baseed, should not be! --EMJ

		return (($this->_id !== NULL && $model->_id !== NULL) && ($this->_id == $model->_id));
	}
}
?>
