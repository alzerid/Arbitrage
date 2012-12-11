<?
namespace Framework\Database2\Drivers\Mongo;

class CQueryModel extends \Framework\Database2\Model\CQueryModel
{
	/**
	 * Method converts a native DataTypes into model DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	public function convertNativeToModel(array &$data, $defaults=NULL)
	{
		static $model_defaults = array();

		//Get defualts
		if($defaults==NULL)
		{
			$class = \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($this->_model);
			if(!isset($model_defaults[$class]))
				$model_defaults[$class] = $class::defaults();

			//Get defaults
			$defaults = $model_defaults[$class];
		}

		//Go thorugh each key value and convert
		foreach($data as $key => $val)
		{
			//TODO: Convert structures!
			if(is_array($val))
			{
				if($defaults[$key] instanceof \Framework\Database2\Model\Structures\CArray)
				{
					$class = NULL;
					//TODO: Get class model in array
					//TODO: Get defaults from class model in array

					//Convert what's in the array
					$this->convertNativeToModel($val, array());

					//Convert to CArray
					$data[$key] = new \Framework\Database2\Model\Structures\CArray($class, $val);
					//$data[$key]->merge();
				}
				elseif($defaults[$key] instanceof \Framework\Database2\Model\Structures\CHash)
				{
					die("HASH " . __METHOD__);
				}
				else
				{
					die("UNKNOWN " . __METHOD__);
				}
			}
			elseif($val instanceof \MongoId)
				$data[$key] = new \Framework\Database2\Model\DataTypes\CDatabaseID((string) $val);
			elseif($val instanceof \MongoDate)
				$data[$key] = new \Framework\Database2\Model\DataTypes\CDate($val->sec);
		}
	}

	/**
	 * Method converts a model DataTypes into native DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	public function convertModelToNative(array &$data)
	{
		foreach($data as $key => $val)
		{
			if($val instanceof \Framework\Database2\Model\DataTypes\CDataType)
				$data[$key] = $this->_convertModelToNativeDataType($val);
			elseif($val instanceof \Framework\Database2\Model\Structures\CStructure)
				$data[$key] = $this->_convertModelToNativeStructure($val);
			elseif($val instanceof \Framework\Database2\Model\CModel)
			{
				die(__METHOD__ . " HANDLE MODEL");
			}
		}
	}

	/**
	 * Method converts a model datatype to native.
	 * @param $value The value to convert.
	 * @return Returns the new converted value.
	 */
	private function _convertModelToNativeDataType(\Framework\Database2\Model\DataTypes\CDataType $val)
	{
		if($val instanceof \Framework\Database2\Model\DataTypes\CDatabaseID)
		{
			$val = (($val->getValue()!==NULL)? $val->getValue() : str_repeat('0', 24));
			return new \MongoId($val);
		}
		elseif($val instanceof \Framework\Database2\Model\DataTypes\CDate)
			return new \MongoDate($val->getTimestamp());
		elseif($val instanceof \Framework\Database2\Model\DataTypes\CEnum)
			return $val->getValue();

		//Throw exception
		throw new \Framework\Exceptions\EDatabaseDataTypeException("Unable to convert DataType.");
	}

	/**
	 * Method converts a model structure to native.
	 * @param $value The value to convert.
	 * @return The newly converted data.
	 */
	private function _convertModelToNativeStructure(\Framework\Database2\Model\Structures\CStructure $val)
	{
		if($val instanceof \Framework\Database2\Model\Structures\CArray)
		{
			$class = $val->getClass();
			$data  = $val->getData();
			$ret   = array();

			//Get class
			if($class)
			{
				foreach($data as $key=>$val)
				{
					//Get data and convert
					$vdata = $val->getData();
					$this->convertModelToNative($vdata);
					$ret[$key] = $vdata;
				}
			}

			return (($class)? $ret : $data);
		}

		//Throw exception
		throw new \Framework\Exceptions\EDatabaseDataTypeExceptoin('Unable to convert Structure.');
	}
}
?>
