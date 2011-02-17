<?
class MongoModel extends Model
{
	public function normalize()
	{
		if(array_key_exists('_id', $this->_variables))
			$this->_variables['_id'] = new MongoId($this->_variables['_id']);

		//Type casting
		$this->_typeCastValues();
	}

	public function fromForm($a)
	{
	}

	public function toForm()
	{
	}

	private function _getDotNotationValue($property)
	{
		$ex = explode('.', $property);

		$value = $this->_variables;
		foreach($ex as $idx)
			$value = $value[$idx];

		return $value;
	}

	private function _setDotNotationValue($property, $value)
	{
		$ex  = explode('.', $property);
		$idx = '';

		foreach($ex as $ref)
			$idx .= "['$ref']";

		eval('$this->_variables' . $idx . '=$value;');

		$value = $this->_variables;
		foreach($ex as $idx)
			$value = $value[$idx];

		return $value;
	}


	private function _typeCastValues()
	{
		$casting = $this->_typeCasting();
		foreach($casting as $cast=>$properties)
		{
			if($cast == "int")
			{
				foreach($properties as $property)
				{
					$value = $this->_getDotNotationValue($property);
					$this->_setDotNotationValue($property, (int) $value);
				}
			}
		}
	}

	protected function _typeCasting()
	{
		return array();
	}

	/* Sets up a mongo $set query and returns
	 * the _id (if one exists) and query array. */
	public function toQuery($filter=array())
	{
		$vars = $this->_variables;
		unset($vars['_id']);  //take out id

		//Take out anything in the filter
		if(count($filter))
		{
			$keys = array_keys($vars);
			foreach($filter as $f)
			{
				if(in_array($f, $keys))
					unset($vars[$f]);
			}
		}

		$query = $this->_toQuery($vars);
		return $query;
	}

	public function _toQuery($vars, $pre="")
	{
		$query = array();
		foreach($vars as $key=>$value)
		{
			if($pre != "")
				$key = "$pre.$key";

			if(is_array($value) && count($value))
				$query = array_merge($query, $this->_toQuery($value, $key));
			elseif(is_array($value))  //empty array
				$query = array_merge($query, array($key => array()));
			else
				$query = array_merge($query, array($key => $value));
		}

		return $query;
	}
}
?>
