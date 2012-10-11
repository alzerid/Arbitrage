<?
namespace Framework\Database\Drivers\Mongo;

class CMongoModelQuery extends \Framework\Database\CDriverQuery
{
	public function findOne($query=array())
	{
		$this->_cmd   = 'findOne';
		$this->_query = $query;

		return $this;
	}

	public function findAll($query=array())
	{
		$this->_cmd   = 'find';
		$this->_query = $query;

		return $this;
	}

	/*public function count($query=array())
	{
		die('count');
		$this->_cmd   = 'count';
		$this->_query = $query;

		return $this;
	}*/

	public function update($query, $data)
	{
		$this->_cmd   = "update";
		$this->_query = $query;
		$this->_data  = $data;

		//Execute
		return $this->execute();
	}

	public function upsert($query, $data)
	{
		die('upsert');
		$this->_cmd   = "upsert";
		$this->_query = $query;
		$this->_data  = $data;

		return $this;
	}

	public function insert($data)
	{
		die('insert');
		$this->_cmd   = "insert";
		$this->_query = NULL;
		$this->_data  = $data;

		return $this;
	}

	public function save($data)
	{
		$this->_cmd   = "save";
		$this->_query = NULL;
		$this->_data  = $data;

		//Execute
		return $this->execute();
	}

	public function remove($query)
	{
		$this->_cmd   = "remove";
		$this->_query = $query;
		$this->_data  = NULL;

		return $this;
	}

	public function execute()
	{
		//Execute command
		$class = $this->_class;
		$prop  = array_merge($this->_driver->getConfig(), $class::properties());

		//Ensure database and table is set
		if(empty($prop['database']))
			throw new EDatabaseDriverException("Database is not set.");

		if(empty($prop['table']))
			throw new EDatabaseDriverException("Table is not set.");

		//Get handle
		$handle = $this->_driver->getHandle();
		$handle = $handle->{$prop['database']}->{$prop['table']};

		//Normalize the query
		$query = $this->_normalizeQuery($this->_query);

		//Normalize the data
		if($this->_data !== NULL)
			$this->_data = $this->_normalizeData($this->_data);

		//Query
		if(in_array($this->_cmd, array('find', 'findOne', 'count')))
		{
			$res = $handle->{$this->_cmd}($query);
			if($res === NULL)
				return NULL;

			if($this->_cmd == "find")
			{
				//Sort
				if($this->_sort !== NULL)
					$res->sort($this->_sort);

				//Limit
				if($this->_limit !== NULL)
					$res->limit($this->_limit);

				return $res;
			}
			elseif($this->_cmd == "findOne" && !empty($res))
			{
				$class = $this->getClass();
				$model = new $class($res, $this->getDriver());
				$model->merge();

				return $model;
			}
			else
				return $res;
		}
		elseif($this->_cmd == "update")
		{
			//Setup update
			$update = array('$set' => $this->_smartFlatten($this->_data));

			//Setup conditions
			$query = \Framework\Utils\CArrayObject::instantiate($query);
			$query = $query->flatten()->toArray();

			//Update
			$ret = $handle->update($query, $update);
		}
		elseif($this->_cmd == "upsert")
		{
			die(__METHOD__ . " UPSERT");

			//Setup update
			$data = array('$set' => $this->_smartFlatten($this->_data));
			$cond = new \CArrayObject($query);
			$cond = $cond->flatten()->toArray();

			//Upsert
			$ret = $handle->update($cond, $data, array('upsert' => true));
		}
		elseif($this->_cmd == "insert")
		{
			die("CMongoModel::execute INSERT");
			//TODO: Do not insert if data has an id
		}
		elseif($this->_cmd == "save")
		{
			$handle->save($this->_data);
			return $this->_data['_id'];
		}
		elseif($this->_cmd == "remove")
			$handle->remove($query);
		else
			throw new EModelException("Cannot do batch operation on '{$this->_cmd}'.");

		return NULL;
	}

	/* Array Access */
	public function offsetExists($offset)
	{
		die(__METHOD__);
	}

	public function offsetGet($offset)
	{
		$this->_getCollection();
		return $this->_collection[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new \EArbitrageException("Unable to set offset for Model Results.");
	}

	public function offsetUnset($offset)
	{
		throw new \EArbitrageException("Unable to unset offset for Model Results.");
	}
	/* End Array Access */

	protected function _getCollection()
	{
		//Check collection
		if($this->_collection)
			return $this->_collection;

		//Get collection
		$this->_collection = new \Framework\Database\Drivers\Mongo\CDatabaseModelCollection($this, $this->execute());
		return $this->_collection;
	}

	private function _smartFlatten($arr, $namespace='')
	{
		$ret = array();
		foreach($arr as $key=>$val)
		{
			$key = (($namespace == "")? $key : "$namespace.$key");
			if(is_array($val) && count($val) > 0 && (array_keys($val) !== range(0, count($val)-1))) //Ignore numerical arrays
				$ret = array_merge($ret, $this->_smartFlatten($val, $key));
			else
				$ret[$key] = $val;
		}

		return $ret;
	}

	private function _normalizeQuery($query)
	{
		//TODO: Convert ALL structures and data types

		//Check query
		if($query === NULL)
			return array();

		$ret = array();
		foreach($query as $key=>$val)
		{
			if($val instanceof \Framework\Interfaces\IModelDataType)
				$value = $this->_driver->convertModelDataTypeToNativeDataType($val);
			else
				$value = $val;

			$ret[$key] = $value;
		}

		return $ret;
	}

	/**
	 * Method normalizes Data Types.
	 * @param $data The data to normalize.
	 */
	private function _normalizeData($data)
	{
		$ret = array();
		foreach($data as $key=>$val)
		{
			if($val instanceof \Framework\Interfaces\IModelDataType)
				$val = $this->_driver->convertModelDataTypeToNativeDataType($val);
			elseif(is_array($val))
				$val = $this->_normalizeData($val);

			//Set value
			$ret[$key] = $val;
		}

		return $ret;
	}
}
?>
