<?
namespace Arbitrage2\DB2;

abstract class CModel
{
	private $_variables;

	public function __construct()
	{
		$defaults          = self::defaults();
		$this->_variables  = new CModelData($defaults);
	}

	public function __get($name)
	{
		return ((isset($this->_variables->$name))? $this->_variables->$name : NULL);
	}

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

		//Get model
		$model = new $class;
		$model->_setData($data);

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

	static public function defaults()
	{
		return static::defaults();
	}

	private function _setData(array $data)
	{
		$this->_variables->merge(new CModelData($data));
	}
}
?>
