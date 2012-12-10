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
		}
	}

	/**
	 * Method converts a model DataTypes into native DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	public function convertModelToNative(array &$data)
	{
		var_dump($this->_model);
		die(__METHOD__);
	}
}
?>
