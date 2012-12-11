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

	public function getQueryDriver($class)
	{
		return new CMongoModelQuery($this, $class);
	}

	public function getBatchDriver()
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
			return new \Framework\Model\DataTypes\CDateDataType($data->sec);
		elseif($data instanceof \MongoId)
			return new \Framework\Database\DataTypes\CDatabaseIDDataType((string) $data);
		elseif(is_object($data))
			throw new \Framework\Exceptions\EDatabaseDriverException("Unable to convert native data type '" . get_class($data) . "' to model type.");

		throw new \Framework\Exceptions\EDatabaseDriverException("Unable to convert native data type '" . gettype($data) . "' to model type with value of '$data'.");
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
		elseif($data instanceof \Framework\Database\DataTypes\CDatabaseIDDataType)
			return new \MongoId($data->getValue());

		throw new \Framework\Exceptions\EDatabaseDriverException("Unable to convert model data type '" . get_class($data) . " to native type.");
	}

	/**
	 * Method converts model structure to native structure.
	 * @param $data The data to convert.
	 * @return Returns the the Model Structure.
	 */
	public function convertModelStructureToNativeStructure(\Framework\Interfaces\IDatabaseModelStructure $data)
	{
		//Grab class and namespace
		$class     = "\\" . __NAMESPACE__ . "\\" . preg_replace('/Framework\\\Database\\\/i', '', get_class($data));
		$namespace = \Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($class);

		//Lazy load
		$ret = \Framework\Base\CKernel::getInstance()->requireFile($namespace, false);
		if(!$ret)
			throw new \Framework\Exceptions\EDatabaseDriverException("Unable to require structure '$namespace'.");

		//Convert to native structure type
		$native = new $class($data);

		//Set driver
		$native->setDriver($this);

		return $native;
	}

	/**
	 * Method converts native structure to model structure.
	 * @param $data The data to convert.
	 * @return Returns the the Native Structure.
	 */
	public function convertNativeStructureToModelStructure(\Framework\Interfaces\IDatabaseModelStructure $data)
	{
		//Ensure the structure is not already in native format
		$class = get_class($data);
		if(preg_match('/Framework\\\Database\\\Structures/', $class))
			return $data;

		//Convert
		throw new \Exception("Code converting of native struct to model struct");
	}

	/**
	 * Method converts the native ID data type to a model id data type.
	 * @param \MongoId $id The id to convert.
	 * @param \Framework\Database\DataType\CDatabaseIDDataType Returns the Model ID data type.
	 */
	public function convertNativeIDtoModelID($id)
	{
		return new \Framework\Database\DataTypes\CDatabaseIDDataType((string) $id);
	}

	/**
	 * Method converts the native ID data type to a model id data type.
	 * @param \Framework\Database\DataType\CDatabaseIDDataType $id The id to convert.
	 * @param \MongoId Returns the Model ID data type.
	 */
	public function convertModelIDtoNativeID(\Framework\Database\DataTypes\CDatabaseIDDataType $id)
	{
		return new \MongoId($id->getValue());
	}
}
?>
