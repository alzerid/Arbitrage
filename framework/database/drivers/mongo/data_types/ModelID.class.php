<?
namespace Framework\Database\Drivers\Mongo\DataTypes;

class ModelID implements \Framework\Interfaces\IModelDataType
{
	private $_mongo_id;

	public function __construct($data=NULL)
	{
		//Create new mongo_id
		if(is_string($data))
			throw new \Framework\Exceptions\EModelDataTypeException("Invalid value as string.");
		elseif(is_int($data))
		{
			$data = dechex($data);
			$this->_mongo_id = new \MongoId(str_repeat('0', 24-strlen($data)) . $data);
		}

		die("Mongo\DataTypes\ModelID::__construct");
	}
}
?>
