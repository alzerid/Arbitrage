<?
namespace Framework\Database\Drivers\Mongo\Structures;

class CArrayStructure extends \Framework\Database\Structures\CArrayStructure
{
	/**
	 * Method returns the updated query.
	 * @param $prened The key string to prepend.
	 * @return array Retuns an array of the updated items.
	 */
	public function getUpdateQuery($prepend=NULL)
	{
		//Iterate through
		$ret  = array();
		foreach($this->_data as $key=>$val)
		{
			$value = NULL;
			$pkey  = (($prepend!==NULL)? "$prepend.$key" : $key);
			if($val instanceof \Framework\Database\CModel)
				$ret = array_merge($ret, $val->getUpdateQuery($pkey));
			elseif($val instanceof \Framework\Interfaces\IDatabaseModelStructure)
			{
				$struct = $this->_driver->convertModelStructureToNativeStructure($val);
				$value  = $struct->getUpdateQuery($pkey);
			}
			elseif($val instanceof \Framework\Interfaces\IModelDataType)
				$value = $this->_driver->convertModelDataTypeToNativeDataType($val);
			else
				$value = $val;

			//Set value
			if($value!==NULL)
			{
				if(is_array($value))
					$ret = array_merge($ret, $value);
				else
					$ret[$pkey] = $value;
			}
		}

		return $ret;
	}

	/**
	 * Method returns the query expression.
	 * @return array Returns an array of the items.
	 */
	public function getQuery()
	{
		return $this->_data;
	}
}
?>
