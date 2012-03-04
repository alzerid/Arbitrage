<?
class CArrayManipulatorException extends Exception
{
}

class CArrayManipulator
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

	public function setValue($expression, $value)
	{
		$expression = explode('.', $expression);
		$data       =& $this->_data;
		foreach($expression as $unit)
		{
			if(isset($data[$unit]))
				$data =& $data[$unit];
			else
				return;
		}

		$data = $value;
	}

	public function arrayDiff($other)
	{
		$this->_data = self::_arrayDiffValue($this->_data, $other);
	}

	public function deepAdd($other)
	{
		$this->_data = self::_deepAdd($this->_data, $other->_data);
	}

	public function mapValues($values, $default=NULL)
	{
		$ret = array();

		//Iterate through values
		foreach($values as $key => $val)
		{
			$ret[$val] = $this->getValue($key);
			if($ret[$val] === NULL && $default !== NULL)
				$ret[$val] = $default;
			elseif($ret[$val] === NULL && $default === NULL)
				unset($ret[$val]);
		}

		return $ret;
	}

	public function toDotNotation()
	{
		return self::_toDotNotation($this->_data);
	}

	//Diff only if values are different and if $arr2 has a key that $arr1 doesnt
	static private function _arrayDiffValue($arr1, $arr2)
	{
		//go through each array
		$ret  = array();
		$arr1 = self::_toDotNotation($arr1);
		$arr2 = self::_toDotNotation($arr2);

		//Array diff from arr2
		$arr1['test'] = false;
		$ret  = array_diff_assoc($arr2, $arr1);

		//To array
		return self::_toArray($ret);
	}

	static private function _deepAdd($arr1, $arr2)
	{
		$ret = array();

		if(is_array($arr2))
		{
			$diff = array_diff_key($arr1, $arr2);
			if(count($diff))
				throw new CArrayManipulatorException("Array key mismatch!");

			foreach($arr1 as $key => $val)
				$arr1[$key] = self::_deepAdd($val, $arr2[$key]);
		}
		else
			return $arr1 + $arr2;

		return $arr1;
	}

	static private function _toArray($arr)
	{
		$ret = array();
		foreach($arr as $key => $val)
		{
			$ex  = explode('.', $key);
			$arr =& $ret;
			foreach($ex as $nval)
			{
				if(!isset($arr[$nval]))
					$arr[$nval] = array();

				$arr =& $arr[$nval];
			}

			$arr = $val;
		}

		return $ret;
	}

	static private function _toDotNotation($vars, $pre="")
	{
		$ret = array();
		foreach($vars as $key=>$value)
		{
			if($pre	!= "")
				$key = "$pre.$key";
			
			if(is_array($value) && count($value))
				$ret = array_merge($ret, self::_toDotNotation($value, $key));
			elseif(is_array($value))  //empty array
				$ret = array_merge($ret, array($key => array()));
			else
				$ret = array_merge($ret, array($key => $value));
		}

		return $ret;
	}
}
?>
