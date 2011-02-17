<?
class MongoModel extends Model
{
	public function normalize()
	{
		if(array_key_exists('_id', $this->_variables))
			$this->_variables['_id'] = new MongoId($this->_variables['_id']);
	}

	public function fromForm($a)
	{
	}

	public function toForm()
	{
	}


	/* Sets up a mongo $set query and returns
	 * the _id (if one exists) and query array. */
	public function toQuery()
	{
		$vars = $this->_variables;
		unset($vars['_id']);  //take out id

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

			if(is_array($value))
				$query = array_merge($query, $this->_toQuery($value, $key));
			else
				$query = array_merge($query, array($key => $value));
		}

		return $query;
	}
}
?>
