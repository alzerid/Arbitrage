<?
namespace Arbitrage2\Database;

class CMongoModelQuery extends CModelQuery
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

	public function count($query=array())
	{
		$this->_cmd   = 'count';
		$this->_query = $query;

		return $this;
	}

	public function update($query, $data)
	{
		$this->_cmd   = "update";
		$this->_query = $query;
		$this->_data  = $data;

		return $this;
	}

	public function upsert($query, $data)
	{
		$this->_cmd   = "upsert";
		$this->_query = $query;
		$this->_data  = $data;

		return $this;
	}

	public function insert($data)
	{
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

		return $this;
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
		$prop  = $class::properties();

		//Query
		//TODO: DB Factory
		$dbconfig = ((isset($prop['config']))? $prop['config'] : '_default');
		$handle   = CDatabaseDriverFactory::getInstance()->getHandle('mongo', $dbconfig);
		$handle   = $handle->{$prop['database']}->{$prop['table']};

		//Query
		if(in_array($this->_cmd, array('find', 'findOne', 'count')))
		{
			$res = $handle->{$this->_cmd}($this->_query);
			if($res === NULL)
				return NULL;

			if($this->_cmd == "find")
			{
				//Sort
				if($this->_sort !== NULL)
					$res = $res->sort($this->_sort);
					
				//Limit
				if($this->_limit !== NULL)
					$res = $res->limit($this->_limit);

				//Create ModelList
				if($res->count() > 0)
				{
					$list = new CMongoModelResults($res, $this->_class);
					return $list;
				}
			}
			elseif($this->_cmd == "count")
				return $res;
			else
			{
				$class = $this->_class;
				return $class::model($res);
			}
		}
		elseif($this->_cmd == "update")
		{
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

			//Setup update
			$data = array('$set' => $this->_smartFlatten($this->_data));
			$cond = new \CArrayObject($this->_query);
			$cond = $cond->flatten()->toArray();

			//Upsert
			$ret = $handle->update($cond, $data, array('upsert' => true));
		}
		elseif($this->_cmd == "insert")
		{
			die("Code insert!");
			//TODO: Do not insert if data has an id
		}
		elseif($this->_cmd == "save")
		{
			$handle->save($this->_data);
			return $this->_data['_id'];
		}
		elseif($this->_cmd == "remove")
			$handle->remove($this->_query);
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
