<?
class MongoModel extends Model
{
	public function normalize()
	{
		if(array_key_exists('_id', $this->_originals))
			$this->_originals['_id'] = new MongoId($this->_originals['_id']);

		if(array_key_exists('_id', $this->_variables))
			$this->_variables['_id'] = new MongoId($this->_variables['_id']);

		//Type casting
		$this->_typeCastValues();
	}

	public function findAll($condition = array())
	{
		$mongo = MongoFactory::getInstance();
		$db    = $this->_db;
		$table = $this->_table;
		$class = $this->_class;

		//Find entry
		$rows = $mongo->$db->$table->find($condition);
		$ret  = array();
		foreach($rows as $row)
			$ret[] = new $class($row);

		return $ret;
	}

	public function findOne($condition = array())
	{
		$mongo = MongoFactory::getInstance();
		$db    = $this->_db;
		$table = $this->_table;
		$class = $this->_class;

		//Find entry
		$ret = $mongo->$db->$table->findOne($condition);

		return (($ret!=NULL)? new $class($ret) : $ret);
	}

	public function remove($condition = array())
	{
		$mongo = MongoFactory::getInstance();
		$db    = $this->_db;
		$table = $this->_table;
		$class = $this->_class;

		//Find entry
		$mongo->$db->$table->remove($condition);
	}

	//Function only updates the entry
	public function update()
	{
		$mongo = MongoFactory::getInstance();
		$db    = $this->_db;
		$table = $this->_table;
		$class = $this->_class;	

		//Get variables
		$vars = $this->_toDotNotation($this->_variables);
		$cond = array('_id' => $vars['_id']);
		unset($vars['_id']);

		$set = array('$set' => $vars);
		$mongo->$db->$table->update($cond, $set);
	}

	public function save()
	{
		$mongo = MongoFactory::getInstance();
		$save  = $this->toArray();

		//save the entity
		$db    = $this->_db;
		$table = $this->_table;

		//Save to db
		$mongo->$db->$table->save($save);
	}

	protected function _getDotNotationValues($notation, &$subject, &$values)
	{
		if(!is_array($notation))
			$notation = explode(".", $notation);

		$key = $notation[0];
		if($key == "$") //Pivot array inidcator
		{
			$val   = &$subject;
			$count = count($val);
			array_shift($notation);

			for($i=0; $i<$count; $i++)
				$this->_getDotNotationValues($notation, $val[$i], $values);
		}
		elseif(count($notation) > 1)   //Keep going
		{
			//Current key
			$val = &$subject[$key];
			array_shift($notation);

			//recursive
			$this->_getDotNotationValues($notation, $val, $values);
		}
		elseif(isset($subject[$key]))
			$values[] = &$subject[$key];
	}

	private function _typeCastValues()
	{
		$casting = $this->_typeCasting();
		foreach($casting as $cast=>$properties)
		{
			foreach($properties as $property)
			{
				//Check if the variable is even set
				$orig_vals = array();
				$mod_vals  = array();
				$this->_getDotNotationValues($property, $this->_originals, $orig_vals);
				$this->_getDotNotationValues($property, $this->_variables, $mod_vals);

				foreach($orig_vals as &$val)
					$val = $this->_typeCastValue($cast, $val);

				foreach($mod_vals as &$val)
					$val = $this->_typeCastValue($cast, $val);
			}
		}
	}

	protected function _typeCastValue($cast, $value)
	{
		$cast = strtolower($cast);
		switch($cast)
		{
			case "int":
				return ((int) $value);
			
			case "float":
				return ((float) $value);

			case "string":
				return ((string) $value);

			case "boolean":
			case "bool":

				if(isset($value))
				{
					if(is_string($value) && ((strtolower($value) === "true" || strtolower($value) === "on")))
						return true;
					elseif(is_numeric($value) && $value != 0)
						return true;
					elseif($value === true)
						return true;
				}

				return false;

			case "mongodate":
				if(!is_object($value) && is_numeric($value))
					return new MongoDate($value);
				elseif(is_string($value))        //Date formatted string
					return new MongoDate(strtotime($value));
				elseif($value === "")
					return new MongoDate(0);

				return $value;

			case "mongoid":
				if(!is_object($value))
					return new MongoId($value);

				return $value;
		}
		
		return $value;
	}

	protected function _typeCasting()
	{
		return array();
	}

	protected function _toDotNotation($vars, $pre="")
	{
		$query = array();
		foreach($vars as $key=>$value)
		{
			if($pre != "")
				$key = "$pre.$key";

			if(is_array($value) && count($value))
				$query = array_merge($query, $this->_toDotNotation($value, $key));
			elseif(is_array($value))  //empty array
				$query = array_merge($query, array($key => array()));
			else
				$query = array_merge($query, array($key => $value));
		}

		return $query;
	}
}
?>
