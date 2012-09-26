<?
namespace Framework\Database\Drivers\Mongo\Structures;

class CHashStructure extends \Framework\Database\Structures\CHashStructure
{
	
	public function __construct(\Framework\Interfaces\IDatabaseModelStructure $struct)
	{
		parent::__construct(\Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($struct->_class), $struct->toArray());
	}

	/**
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery()
	{
		die(__METHOD__);
		//TODO: Code smarter differences
		if(count($this->_data) == 0)
			return $this->_data;

		$ret = array_diff($this->_data, $this->_originals);
		if(count($ret) == 0)
			return NULL;

		return $this->_data;
	}

	/**
	 * Method returns the query.
	 */
	public function getQuery()
	{
		$ret = array();
		foreach($this->_data as $key=>$value)
		{
			$value = $this->$key;
			if($value instanceof \Framework\Interfaces\IModelDataType)
				$value = $this->_driver->convertModelDataTypeToNativeDataType($value);
			elseif($value instanceof \Framework\Database\CModel)
			{
				$value->setDriver($this->_driver);
				$value = $this->getQuery();
			}
			elseif($value instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				//Convert Native structure to Model
				$struct = $this->_driver->convertModelStructureToNativeStructure($value);
				$struct->setDriver($this->_driver);

				//Get query
				$value = $struct->getQuery();
			}
			elseif(is_object($value))
			{
				var_dump($key, $value);
				throw new \Framework\Exceptions\EModelDataException("Unable to handle query conversion");
			}

			//Assign values
			$ret[$key] = $value;
		}

		return ((count($ret)===0)? NULL : $ret);
	}

}
?>
