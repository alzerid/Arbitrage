<?
namespace Arbitrage2\DB2;

class CModelData extends \CArrayObject
{
	public function __set($name, $val)
	{
		if(!isset($this->_data[$name]))
			throw new EModelException("Data point '$name' not in definitions.");

		$this->_data[$name] = $val;
	}

	public function merge(CModelData $data)
	{
		CModelData::mergeArrayData($this->_toArrayReference(), $data->_toArrayReference());
	}

	static public function mergeArrayData(array &$arr1, array &$arr2)
	{
		//TODO: Throw error if arr2 has a key that arr1 doesnt have --EMJ

		//Iterate through each element
		foreach($arr1 as $key => &$value)
		{
			if(!isset($arr2[$key]))
				continue;

			if($value instanceof CModelListData)
			{
				$list = new CModelListData($value->getClass());
				var_dump($arr2[$key], $value);
				die();
				$value->merge($list);
			}
			elseif($value instanceof CModelData)
			{
				//Create new class
				$class = get_class($value);
				$class = new $class();
				$value->merge($class);
			}
			elseif(is_array($value))
			{
				die("ARRAY");
			}
			else //Normal value
				$arr1[$key] = $arr2[$key];
		}
	}
}
?>
