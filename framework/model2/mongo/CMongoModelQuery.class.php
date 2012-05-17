<?
namespace Arbitrage2\Model2;

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

	public function update($query, $data)
	{
		$this->_cmd   = "update";
		$this->_query = $query;
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

	public function execute()
	{
		//TODO: DB Factory
		
		//Execute command
		$class = $this->_class;
		$prop  = $class::properties();

		//Query
		$handle = new \Mongo;
		$handle = $handle->{$prop['database']}->{$prop['table']};

		//Query
		if(in_array($this->_cmd, array('find', 'findOne')))
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
			else
			{
				$class = $this->_class;
				return $class::model($res);
			}
		}
		elseif($this->_cmd == "update")
		{
			//Setup update
			$update = new \CArrayObject($this->_data);
			$update = array('$set' => $update->flatten()->toArray());

			//Setup conditions
			$query = new \CArrayObject($this->_query);
			$query = $query->flatten()->toArray();

			//Update
			$ret = $handle->update($query, $update);
		}
		elseif($this->_cmd == "save")
		{
			$handle->save($this->_data);
			return $this->_data['_id'];
		}
		else
			die("unknown execution {$this->_cmd}");


		return NULL;
	}
}
?>
