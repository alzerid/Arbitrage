<?
namespace Arbitrage2\Model2;

class CMongoModelQuery extends CModelQuery
{
	public function findOne($query)
	{
		$this->_cmd   = 'findOne';
		$this->_query = $query;

		return $this;
	}

	public function findAll($query)
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
		die();
	}

	public function save($query, $data)
	{
		die("SAVE");
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
		else
			die("unknown execution {$this->_cmd}");
	}
}
?>
