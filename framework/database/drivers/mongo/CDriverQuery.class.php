<?
namespace Framework\Database\Drivers\Mongo;

class CMongoModelQuery extends \Framework\Database\CDriverQuery
{
	public function findOne($query=array())
	{
		$this->_cmd   = 'findOne';
		$this->_query = $query;

		return new CModelResults($this);
	}

	public function findAll($query=array())
	{
		$this->_cmd   = 'find';
		$this->_query = $query;

		return new CModelResults($this);
	}

	public function count($query=array())
	{
		die('count');
		$this->_cmd   = 'count';
		$this->_query = $query;

		return $this;
	}

	public function update($query, $data)
	{
		die('update');
		$this->_cmd   = "update";
		$this->_query = $query;
		$this->_data  = $data;

		return $this;
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

		//Create results class
		$results = new CModelResults($this);

		//Execute
		return $this->execute($results);
	}

	public function remove($query)
	{
		die('remove');
		$this->_cmd   = "remove";
		$this->_query = $query;
		$this->_data  = NULL;

		return $this;
	}

	public function execute(\Framework\Database\CModelResults $results)
	{
		//Execute command
		$class = $this->_class;
		$prop  = array_merge($this->_driver->getConfig(), $class::properties());

		//Get handle
		$handle = $this->_driver->getHandle();
		$handle = $handle->{$prop['database']}->{$prop['table']};

		//Query
		if(in_array($this->_cmd, array('find', 'findOne', 'count')))
		{
			$res = $handle->{$this->_cmd}($this->_query);
			if($res === NULL)
				return NULL;

			if($this->_cmd == "find")
			{
				//Sort
				if($results->getSort() !== NULL)
					$res = $res->sort($results->getSort());

				//Limit
				if($results->getLimit() !== NULL)
					$res = $res->limit($results->getLimit());

				//Create ModelList
				if($res->count() > 0)
					return $res;
			}
			elseif($this->_cmd == "findOne")
				return array($res);
			else
				return $res;
		}
		elseif($this->_cmd == "update")
		{
			die("CMongoModel::execute UPDATE");
			//Setup update
			$update = array('$set' => $this->_smartFlatten($this->_data));
			
			//Setup conditions
			$query = new \CArrayObject($this->_query);
			$query = $query->flatten()->toArray();

			//Update
			$ret = $handle->update($query, $update);
		}
		elseif($this->_cmd == "upsert")
		{
			die("CMongoModel::execute UPSERT");

			//Setup update
			$data = array('$set' => $this->_smartFlatten($this->_data));
			$cond = new \CArrayObject($this->_query);
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
		{
			die("CMongoModel::execute REMOVE");
			$handle->remove($this->_query);
		}
		else
			throw new EModelException("Cannot do batch operation on '{$this->_cmd}'.");

		return NULL;
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
}
?>
