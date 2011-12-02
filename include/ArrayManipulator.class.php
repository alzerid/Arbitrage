<?
class ArrayManipulatorException extends Exception
{
}

class ArrayManipulator
{
	private $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public function setData($data)
	{
		$this->_data = $data;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function getValue($expression)
	{
		$expression = explode('.', $expression);
		$data       = $this->_data;
		foreach($expression as $unit)
		{
			if(isset($data[$unit]))
				$data = $data[$unit];
			else
				return NULL;
		}

		return $data;
	}

	public function deepAdd($other)
	{
		$this->_data = self::_deepAdd($this->_data, $other->_data);
	}

	static private function _deepAdd($arr1, $arr2)
	{
		$ret = array();

		if(is_array($arr2))
		{
			$diff = array_diff_key($arr1, $arr2);
			if(count($diff))
				throw new ArrayManipulatorException("Array key mismatch!");

			foreach($arr1 as $key => $val)
				$arr1[$key] = self::_deepAdd($val, $arr2[$key]);
		}
		else
			return $arr1 + $arr2;

		return $arr1;
	}
}
?>
