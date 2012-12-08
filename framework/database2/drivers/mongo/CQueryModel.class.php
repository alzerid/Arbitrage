<?
namespace Framework\Database2\Drivers\Mongo;

class CQueryModel extends \Framework\Database2\Model\CQueryModel
{
	/**
	 * Method converts a native DataTypes into model DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	protected function _convertNativeToModel(array &$data)
	{
		foreach($data as $key => $val)
		{
			if(is_array($val))
				$this->_convertNativeToModel($val);
			elseif($val instanceof \MongoId)
				$data[$key] = new \Framework\Database2\Model\DataTypes\CDatabaseID((string) $val);
		}
	}

	/**
	 * Method converts a model DataTypes into native DataTypes.
	 * @param $data The data array to convert.
	 * @return The newly converted data array.
	 */
	protected function _convertModelToNative(array &$data)
	{
		die(__METHOD__);
	}
}
?>
