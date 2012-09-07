<?
namespace Framework\Database\Drivers\Mongo;

class CDatabaseDriver extends \Framework\Database\CDatabaseDriver
{
	public function __construct($config)
	{
		parent::__construct($config);

		//Connect
		$uri           = "mongodb://" . ((isset($config['host']))? $config['host'] : '127.0.0.1') . ':' . ((isset($config['port']))? $config['port'] : 27017);
		$this->_handle = new \Mongo($uri);
	}

	public function getQuery($class)
	{
		return new CMongoModelQuery($this, $class);
	}

	public function getBatch()
	{
		die(__METHOD__);
	}

	/**
	 * Method converts native data types to model data types.
	 * @param $data The data to convert.
	 * @return Returns the the Model Data Type.
	 */
	public function convertNativeDataTypeToModelDataType($data)
	{
		//Convert Native Type to Model Type
		if($data instanceof \MongoDate)
			return \Framework\Model\DataTypes\CDateDataType::instantiate($data->sec);

		throw new \Framework\Exceptions\EDatabaseDriverException("Unable to convert native data type '" . get_class($data) . " to model type.");
	}

	/**
	 * Method converts model data type to native data type.
	 * @param $data The data to convert.
	 * @return Returns the the Native Data Type.
	 */
	public function convertModelDataTypeToNativeDataType($data)
	{
		if($data instanceof \Framework\Model\DataTypes\CDateDataType)
			return new \MongoDate($data->getTimestamp());

		throw new \Framework\Exceptions\EDatabaseDriverException("Unable to convert model data type '" . get_class($data) . " to native type.");
	}

	/**
	 * Method converts model structure to native structure.
	 * @param $data The data to convert.
	 * @return Returns the the Model Structure.
	 */
	public function convertModelStructureToNativeStructure($data)
	{
		//Grab class and namespace
		$class     = "\\" . __NAMESPACE__ . "\\" . preg_replace('/Framework\\\Database\\\/i', '', get_class($data));
		$namespace = \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($class);

		//Lazy load
		$ret = \Framework\Base\CKernel::getInstance()->requireFile($namespace, false);
		if(!$ret)
			throw new \Framework\Exceptions\EDatabaseDriverException("Unable to require structure '$namespace'.");

		return $class::instantiate($data);
	}

	/**
	 * Method converts native structure to model structure.
	 * @param $data The data to convert.
	 * @return Returns the the Native Structure.
	 */
	public function convertNativeStructureToModelStructure($data)
	{
		die(__METHOD__);
	}
}
?>
