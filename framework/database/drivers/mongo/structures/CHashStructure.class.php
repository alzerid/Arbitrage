<?
namespace Framework\Database\Drivers\Mongo\Structures;

class CHashStructure extends \Framework\Database\Structures\CHashStructure
{
	
	public function __construct(\Framework\Interfaces\IDatabaseModelStructure $struct)
	{
		parent::__construct(\Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($struct->_class), $struct->toArray());
		$this->_variables = $struct->_variables;
	}

	/**
	 * Method returns the updated query.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery($pkey=NULL)
	{
		//Check count
		if(count($this->_variables) == 0)
			return $this->_data;

		//Iterate through both _data and _variables, _data first
		$ret = array();
		$itr = array('_data', '_variables');
		foreach($itr as $type)
		{
			//Go through values
			if(count($this->$type))
			{
				foreach($this->$type as $key=>$val)
					$ret = array_merge($ret, $val->getUpdateQuery((($pkey!==NULL)? "$pkey.$key" : $key)));
			}
		}

		return $ret;
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
