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

		//TODO: Attempt to reuse objects instead of instantiating new ones

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
					$class = (($defaults[$key]->getClass())? \Framework\Base\CKernel::getInstance()->convertArbitrageNamespaceToPHP($defaults[$key]->getClass()) : NULL);
					if($class)
					{
						$ret       = array();
						$cdefaults = $class::defaults();
						foreach($val as $akey=>$adata)
						{
							//Convert data to model data
							$this->convertNativeToModel($adata, $cdefaults);

							//Create class
							$model = new $class($adata);
							$model->merge();

							//Add to array
							$ret[] = $model;
						}
					}
					else
						$ret = $val;

					//Create CArray
					$data[$key] = new \Framework\Database2\Model\Structures\CArray(\Framework\Base\CKernel::getInstance()->convertPHPNamespaceToArbitrage($class), $ret);
					$data[$key]->merge();
				}
				elseif($defaults[$key] instanceof \Framework\Database2\Model\Structures\CHash)
				{
					throw new \Framework\Exceptions\ENotImplementedException("Conversion from native model to driver for CHash not implemented.");
				}
				elseif($defaults[$key] instanceof \Framework\Database2\Model\CModel)
				{
					//Get class
					$class = get_class($defaults[$key]);

					//Send $val to convertNativeToMOdel
					$this->convertNativeToModel($val, $class::defaults());

					//Create class
					$data[$key] = new $class($val);
					$data[$key]->merge();
				}
				else
				{
					var_dump($val);
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
				$data[$key] = $this->_convertModelDataTypeToNativeDataType($val);
			elseif($val instanceof \Framework\Database2\Model\Structures\CStructure)
				$data[$key] = $this->_convertModelStructureToNativeStructure($val);
			elseif($val instanceof \Framework\Database2\Model\CModel)
				$data[$key] = $this->_convertModelToNative($val);
			elseif(is_object($val))
				throw new \Framework\Exceptions\EDatabaseDataTypeException("Unable to convert DataType '" . get_class($val) . "'.");
		}
	}

	/**
	 * Method converts a model datatype to native.
	 * @param $value The value to convert.
	 * @return Returns the new converted value.
	 */
	private function _convertModelDataTypeToNativeDataType(\Framework\Database2\Model\DataTypes\CDataType $val)
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
	private function _convertModelStructureToNativeStructure(\Framework\Database2\Model\Structures\CStructure $val)
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
		throw new \Framework\Exceptions\EDatabaseDataTypeException('Unable to convert Structure.');
	}


	public function _convertModelToNative(\Framework\Database2\Model\CModel $val)
	{
		//Merge the data
		$val->merge();

		//Return data
		return $val->getData();
	}
}
?>
